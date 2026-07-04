const { test, expect } = require('@playwright/test');

const BASE = 'https://iskconseshadripuram.org';

test.describe('Payment Flow E2E Tests', () => {

  test('donate page loads with cause options', async ({ page }) => {
    await page.goto(`${BASE}/donate/`);
    await expect(page).toHaveTitle(/Donate/i);
    // Check that donation causes are listed
    const causeCards = page.locator('.cause-card, .donate-card, .category-tile, a[href*="donate/"]');
    const count = await causeCards.count();
    expect(count).toBeGreaterThan(0);
  });

  test('donate/daily-seva page loads with donation form', async ({ page }) => {
    await page.goto(`${BASE}/donate/daily-seva`);
    await expect(page).toHaveTitle(/Daily Seva|Donate/i);
    // Should have donation amount options or form
    const formElements = page.locator('form, .donate-form-card, .amount-option, [data-amount]');
    const count = await formElements.count();
    expect(count).toBeGreaterThan(0);
  });

  test('donate/diwali page loads correctly', async ({ page }) => {
    await page.goto(`${BASE}/donate/diwali`);
    await expect(page).toHaveTitle(/Diwali|Donate/i);
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('donation form has required fields', async ({ page }) => {
    await page.goto(`${BASE}/donate/daily-seva`);
    // Check for donor name, email, phone fields
    const nameField = page.locator('input[name="donor_name"], input#donorName, input[placeholder*="name" i]');
    const emailField = page.locator('input[name="donor_email"], input#donorEmail, input[type="email"]');
    const phoneField = page.locator('input[name="donor_phone"], input#donorPhone, input[type="tel"]');
    
    await expect(nameField.first()).toBeVisible({ timeout: 10000 });
    await expect(emailField.first()).toBeVisible({ timeout: 10000 });
    await expect(phoneField.first()).toBeVisible({ timeout: 10000 });
  });

  test('amount selection updates total', async ({ page }) => {
    await page.goto(`${BASE}/donate/daily-seva`);
    // Look for amount buttons/options
    const amountButtons = page.locator('.amount-option, .preset-amount, [data-amount], button:has-text("₹")');
    const count = await amountButtons.count();
    if (count > 0) {
      // Click first amount option
      await amountButtons.first().click();
      // Verify total updates
      const totalDisplay = page.locator('.total-amount, .donation-total, #totalAmount, [class*="total"]');
      if (await totalDisplay.count() > 0) {
        await expect(totalDisplay.first()).not.toContainText('₹0');
      }
    }
  });

  test('API create-order endpoint responds to POST', async ({ page }) => {
    const response = await page.request.post(`${BASE}/api/create-order.php`, {
      headers: { 'Content-Type': 'application/json' },
      data: {
        amount: 1000,
        cause_slug: 'daily-seva',
        cause_id: 1,
        donor_name: 'Test Donor',
        donor_email: 'test@example.com',
        donor_phone: '+91-9999999999',
        donation_mode: 'one_time',
        form_type: 'tiers'
      }
    });
    // Should return JSON (either success or validation error)
    const body = await response.text();
    expect(body).toBeTruthy();
    // Should not return HTML error page
    expect(body).not.toContain('<!DOCTYPE');
    expect(body).not.toContain('<html');
  });

  test('API verify-payment endpoint handles GET gracefully', async ({ page }) => {
    const response = await page.request.get(`${BASE}/api/verify-payment.php`);
    // Should return JSON error, not crash
    const body = await response.text();
    expect(body).toBeTruthy();
    expect(body).not.toContain('<!DOCTYPE');
  });

  test('booking/puja listing page loads', async ({ page }) => {
    await page.goto(`${BASE}/booking/puja/`);
    await expect(page).toHaveTitle(/Puja/i);
    const cards = page.locator('.puja-card');
    await expect(cards.first()).toBeVisible({ timeout: 10000 });
  });

  test('booking/puja detail page has booking form', async ({ page }) => {
    await page.goto(`${BASE}/booking/puja/sri-sri-radha-madhav`);
    await expect(page).toHaveTitle(/Radha Madhav/i);
    // Should have offering cards and booking form
    const offerings = page.locator('.offering-card');
    const count = await offerings.count();
    expect(count).toBeGreaterThan(0);
  });

  test('booking/yagya listing page loads', async ({ page }) => {
    await page.goto(`${BASE}/booking/yagya/`);
    await expect(page).toHaveTitle(/Yagya/i);
    const cards = page.locator('.yagya-card');
    await expect(cards.first()).toBeVisible({ timeout: 10000 });
  });

  test('booking/yagya detail page has tier cards', async ({ page }) => {
    await page.goto(`${BASE}/booking/yagya/sri-sudarshan-narasimha-yagya`);
    await expect(page).toHaveTitle(/Sudarshan/i);
    const tiers = page.locator('.yagya-tier-card');
    const count = await tiers.count();
    expect(count).toBeGreaterThan(0);
  });

  test('panihati registration page loads with form', async ({ page }) => {
    await page.goto(`${BASE}/yatra/panihati`);
    await expect(page).toHaveTitle(/Panihati/i);
    await expect(page.locator('#regName')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('#regPhone')).toBeVisible();
    await expect(page.locator('#regEmail')).toBeVisible();
  });

  test('panihati price calculation works', async ({ page }) => {
    await page.goto(`${BASE}/yatra/panihati`);
    // Default: 1 adult × ₹1,000 = ₹1,000
    await expect(page.locator('#valTotalSummary')).toContainText('₹1,000');
    // Change to 2 adults
    await page.fill('#cntAdults', '2');
    await page.locator('#cntAdults').dispatchEvent('change');
    await expect(page.locator('#valTotalSummary')).toContainText('₹2,000');
  });

  test('panihati own vehicle pricing differs from bus', async ({ page }) => {
    await page.goto(`${BASE}/yatra/panihati`);
    await expect(page.locator('#valTotalSummary')).toContainText('₹1,000');
    // Switch to own vehicle
    await page.locator('#labelVehicle').click();
    await expect(page.locator('#valTotalSummary')).toContainText('₹600');
  });

  test('Razorpay checkout script loads on donate pages', async ({ page }) => {
    await page.goto(`${BASE}/donate/daily-seva`);
    // Script tags are hidden elements — check count instead of visibility
    const checkoutScript = page.locator('script[src*="checkout.razorpay.com"]');
    await expect(checkoutScript).toHaveCount(1, { timeout: 10000 });
  });

  test('all donate cause pages load without 500 errors', async ({ page }) => {
    const causes = [
      'daily-seva', 'nitya-seva', 'food-for-life', 'shastra-daan',
      'tula-daan-utsav', 'donate-a-brick', 'diwali', 'janmashtami',
      'ratha-yatra', 'gaura-purnima', 'govardhan-puja'
    ];
    for (const cause of causes) {
      const response = await page.goto(`${BASE}/donate/${cause}`);
      expect(response.status()).toBe(200);
    }
  });

  test('all booking detail pages load without errors', async ({ page }) => {
    const pujaPages = [
      'sri-sri-radha-madhav', 'sri-sri-gaura-nitai', 'sri-giriraja-sila',
      'sri-saligrama-sila', 'guru-puja', 'anniversary', 'birthday'
    ];
    for (const slug of pujaPages) {
      const response = await page.goto(`${BASE}/booking/puja/${slug}`);
      expect(response.status()).toBe(200);
    }
  });
});
