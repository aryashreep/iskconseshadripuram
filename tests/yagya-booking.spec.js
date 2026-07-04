const { test, expect } = require('@playwright/test');

test.describe('Yagya Booking Flow', () => {

  test('listing page loads with all yagya cards', async ({ page }) => {
    await page.goto('/booking/yagya/');

    await expect(page).toHaveTitle(/Yagya/i);
    await expect(page.locator('h1')).toContainText('Yagya');

    // Should show 5 yagya cards
    const cards = page.locator('.yagya-card');
    await expect(cards).toHaveCount(5);

    // Verify first card content
    const firstCard = cards.first();
    await expect(firstCard.locator('h3')).toContainText('Sudarshan Narasimha');
    await expect(firstCard.locator('.yagya-card-badge')).toContainText('₹501');
    await expect(firstCard.locator('.btn')).toContainText('Sponsor Yagya');
  });

  test('navigation to yagya detail page works', async ({ page }) => {
    await page.goto('/booking/yagya/');

    await page.locator('.yagya-card').first().locator('.btn').click();

    await expect(page).toHaveURL(/\/booking\/yagya\/sri-sudarshan-narasimha-yagya/);
    await expect(page.locator('h1')).toContainText('Sudarshan Narasimha');
  });

  test('yagya detail page renders tiers and info sections', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    // Banner image visible
    await expect(page.locator('img[alt*="Sudarshan"]')).toBeVisible();

    // Info badges (place, date, helpline, whatsapp)
    const infoBadges = page.locator('.yagya-info-badge');
    await expect(infoBadges).toHaveCount(4);

    // Tier cards should exist
    const tiers = page.locator('.yagya-tier-card');
    const tierCount = await tiers.count();
    expect(tierCount).toBeGreaterThanOrEqual(5);

    // Verify tier prices
    await expect(page.locator('.yagya-tier-card').first()).toContainText('₹501');
  });

  test('tier Book Now button opens booking modal', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    // Click first tier's "Book Now"
    await page.locator('.yagya-tier-card').first().locator('.btn').click();

    // Modal should appear with form fields
    const modal = page.locator('#bookingModal');
    await expect(modal).toBeVisible({ timeout: 5000 });

    // Modal should contain form fields (actual IDs from the page)
    await expect(modal.locator('#donorName')).toBeVisible();
    await expect(modal.locator('#donorEmail')).toBeVisible();
    await expect(modal.locator('#donorPhone')).toBeVisible();
  });

  test('booking modal pre-fills tier name and amount', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    // Click "Devotional Offering" tier (₹1,101)
    await page.locator('.yagya-tier-card').nth(1).locator('.btn').click();

    const modal = page.locator('#bookingModal');
    await expect(modal).toBeVisible({ timeout: 5000 });

    // Should show selected tier name in modal title
    await expect(modal.locator('#modalYagyaTitle')).toContainText('Devotional Offering');
    // Amount label should show the price
    await expect(modal.locator('#modalAmountLabel')).toContainText('1,101');
  });

  test('all yagya detail pages load without errors', async ({ page }) => {
    const slugs = [
      'sri-sudarshan-narasimha-yagya',
      'vastu-yagya',
      'dhanvantari-yagya',
      'navagraha-yagya',
      'ayushya-yagya',
    ];

    for (const slug of slugs) {
      const response = await page.goto(`/booking/yagya/${slug}`);
      expect(response.status()).toBe(200);
      await expect(page.locator('h1')).not.toBeEmpty();
    }
  });

  test('yagya detail shows hymns and who should participate sections', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    await expect(page.locator('h3:has-text("Sacred Vedic Hymns")')).toBeVisible();
    await expect(page.locator('h3:has-text("Who Should Participate")')).toBeVisible();
    await expect(page.locator('h3:has-text("Spiritual Benefits")')).toBeVisible();
    await expect(page.locator('h3:has-text("Life Changing Benefits")')).toBeVisible();

    // Hymns list should have items
    const hymnItems = page.locator('li:has-text("Sankalpa")');
    await expect(hymnItems.first()).toBeVisible();
  });

  test('yagya listing cards have correct links', async ({ page }) => {
    await page.goto('/booking/yagya/');

    const expectedSlugs = [
      'sri-sudarshan-narasimha-yagya',
      'vastu-yagya',
      'dhanvantari-yagya',
      'navagraha-yagya',
      'ayushya-yagya',
    ];

    for (const slug of expectedSlugs) {
      const link = page.locator(`a[href*="${slug}"]`);
      await expect(link).toBeVisible();
    }
  });

  test('back navigation from yagya detail to listing', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    await page.locator('.breadcrumb a:has-text("Yagyas")').click();
    await expect(page).toHaveURL(/\/booking\/yagya\/?$/);
  });

  test('tier cards show inclusion badges', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    // First tier should have basic inclusions
    const firstTier = page.locator('.yagya-tier-card').first();
    await expect(firstTier.locator('.yagya-inc-badge')).toHaveCount(2); // Offering + Oblation

    // Last tier should have many inclusions
    const lastTier = page.locator('.yagya-tier-card').last();
    const lastTierInclusions = await lastTier.locator('.yagya-inc-badge').count();
    expect(lastTierInclusions).toBeGreaterThanOrEqual(8);
  });

  test('higher tiers show divine returns and shipping info', async ({ page }) => {
    await page.goto('/booking/yagya/sri-sudarshan-narasimha-yagya');

    // Tier 4 (Shraddha Bhakti Seva) should show divine returns
    const tier4 = page.locator('.yagya-tier-card').nth(3);
    await expect(tier4).toContainText('Divine Returns');
    await expect(tier4).toContainText('Shipping team will contact');
  });
});
