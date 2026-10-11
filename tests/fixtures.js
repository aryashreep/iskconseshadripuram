/**
 * Shared Playwright fixtures.
 *
 * Two third-party services the site loads are unreliable or unreachable from
 * some networks (a dev machine behind a restrictive ISP, or CI with limited
 * egress):
 *
 *   - https://wttr.in/...        weather widget, fetched in assets/js/main.js
 *   - https://picsum.photos/...  fallback images/backgrounds across templates
 *
 * When those requests hang instead of failing, they starve the browser and
 * tests trip their 60s timeout. Both are stubbed with valid responses so page
 * content still renders (images stay visible, the weather label still renders).
 *
 * Only these two hosts are intercepted. Blocking *all* third-party traffic was
 * tried and reverted: it also removed stylesheets the layout depends on
 * (Font Awesome via cdnjs), which broke unrelated tests such as the mobile
 * menu toggle.
 *
 * Set E2E_REAL_EXTERNAL=1 to bypass the stubs and exercise the real services.
 */
const base = require('@playwright/test');

// 1x1 transparent GIF. A valid image, so <img> elements and CSS
// background-image still render (tests assert these are visible).
const PIXEL_GIF = Buffer.from(
  'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
  'base64'
);

// Shape expected by assets/js/main.js:
//   json.current_condition[0].temp_C / .weatherDesc[0].value
const WEATHER_JSON = JSON.stringify({
  current_condition: [{ temp_C: '28', weatherDesc: [{ value: 'Sunny' }] }],
});

async function stubExternalServices(page) {
  if (process.env.E2E_REAL_EXTERNAL === '1') return;

  await page.route(/wttr\.in/, (route) =>
    route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: WEATHER_JSON,
    })
  );

  await page.route(/picsum\.photos/, (route) =>
    route.fulfill({
      status: 200,
      contentType: 'image/gif',
      body: PIXEL_GIF,
    })
  );

  // Razorpay's checkout lazily imports its payment-method chunks from this
  // host. They stall indefinitely (18 requests left pending on /yatra/panihati)
  // and are irrelevant to the assertions, which only check markup. The main
  // checkout.razorpay.com script is left alone - payment-flow.spec.js asserts
  // its script tag exists.
  await page.route(/checkout-static-next\.razorpay\.com/, (route) =>
    route.abort()
  );

  // Telemetry/analytics beacons that never complete in this environment and
  // have no effect on rendering: Razorpay's lumberjack logger, the Sentry
  // ingest endpoint its SDK calls, and Google Analytics/Tag Manager.
  await page.route(
    /lumberjack\.razorpay\.com|ingest\.sentry\.io|google-analytics\.com|googletagmanager\.com/,
    (route) => route.abort()
  );
}

// Extend the built-in `page` fixture so every page is stubbed. Overriding
// `page` (rather than adding an auto fixture) keeps tests that only use
// `request` free of any browser overhead.
const test = base.test.extend({
  page: async ({ page }, use) => {
    await stubExternalServices(page);
    await use(page);
  },
});

/**
 * Wait until a page has finished loading its own assets.
 *
 * Replaces `page.waitForLoadState('networkidle')`, which only resolves once
 * *every* connection has been quiet for 500ms. That condition is never reached
 * while any request is still open - a telemetry beacon, a long-poll, an ad or
 * embed - so the wait intermittently burned the full 60s test timeout instead
 * of failing informatively. `load` is the well-defined state that covers the
 * document's stylesheets and scripts, and the short bounded settle afterwards
 * lets deferred scripts run so late console errors are still observed.
 */
async function waitForPageReady(page) {
  await page.waitForLoadState('load');
  await page.waitForTimeout(750);
}

module.exports = { test, expect: base.expect, waitForPageReady };
