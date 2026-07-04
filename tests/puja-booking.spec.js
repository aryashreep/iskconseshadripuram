const { test, expect } = require('@playwright/test');

test.describe('Puja Booking Flow', () => {

  test('listing page loads with all puja cards', async ({ page }) => {
    await page.goto('/booking/puja/');

    await expect(page).toHaveTitle(/Puja/i);

    // Page header
    await expect(page.locator('h1')).toContainText('Puja Offerings');

    // Should show 7 puja cards
    const cards = page.locator('.puja-card');
    await expect(cards).toHaveCount(7);

    // Verify first card content
    const firstCard = cards.first();
    await expect(firstCard.locator('h3')).toContainText('Sri Sri Radha Madhav');
    await expect(firstCard.locator('.puja-card-badge')).toContainText('₹1,008');
    await expect(firstCard.locator('.btn')).toContainText('Offer Puja');

    // Verify all cards have images, titles, and CTA buttons
    for (let i = 0; i < 7; i++) {
      const card = cards.nth(i);
      await expect(card.locator('.puja-card-image')).toBeVisible();
      await expect(card.locator('h3')).not.toBeEmpty();
      await expect(card.locator('.btn')).toBeVisible();
    }
  });

  test('navigation to puja detail page works', async ({ page }) => {
    await page.goto('/booking/puja/');

    // Click first puja card's CTA
    await page.locator('.puja-card').first().locator('.btn').click();

    // Should navigate to detail page
    await expect(page).toHaveURL(/\/booking\/puja\/sri-sri-radha-madhav/);
    await expect(page.locator('h1')).toContainText('Sri Sri Radha Madhav');
  });

  test('puja detail page renders offering categories', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Banner image visible
    await expect(page.locator('img[alt="Sri Sri Radha Madhav"]')).toBeVisible();

    // Offering cards should be present (Garlands, Tulsi, Dry Fruits, Sweets, Attar, Deepdan, Fruits)
    const offeringCards = page.locator('.offering-card');
    const cardCount = await offeringCards.count();
    expect(cardCount).toBeGreaterThanOrEqual(20);
  });

  test('offering selection toggles and updates total', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Total should start at ₹0
    await expect(page.locator('#totalAmountLabel')).toContainText('₹0');

    // Submit button should be disabled initially
    await expect(page.locator('#bookBtn')).toBeDisabled();

    // Click first offering (Marigold Garland ₹201)
    await page.locator('#btn-g1').click();

    // Total should update
    await expect(page.locator('#totalAmountLabel')).toContainText('₹201');

    // Button should now be enabled
    await expect(page.locator('#bookBtn')).not.toBeDisabled();
    await expect(page.locator('#bookBtn')).toContainText('₹201');

    // Cart summary should appear
    await expect(page.locator('#cartSummaryWrap')).toBeVisible();
    await expect(page.locator('#cartItemsList')).toContainText('Marigold Garland');

    // Click same offering again to deselect
    await page.locator('#btn-g1').click();

    // Total back to ₹0
    await expect(page.locator('#totalAmountLabel')).toContainText('₹0');
    await expect(page.locator('#bookBtn')).toBeDisabled();
  });

  test('multiple offerings can be selected and total sums correctly', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Select Marigold Garland (₹201) + 21 Tulsi leaves (₹101) + Coconut (₹151)
    await page.locator('#btn-g1').click();
    await page.locator('#btn-t1').click();
    await page.locator('#btn-sw1').click();

    // Total = 201 + 101 + 151 = ₹453
    await expect(page.locator('#totalAmountLabel')).toContainText('₹453');
    await expect(page.locator('#bookBtn')).toContainText('₹453');

    // Cart should show 3 items
    const cartItems = page.locator('#cartItemsList li');
    await expect(cartItems).toHaveCount(3);
  });

  test('booking form validates required fields', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Select an offering to enable the button
    await page.locator('#btn-g1').click();

    // Try to submit without filling form — HTML5 validation should prevent it
    const bookBtn = page.locator('#bookBtn');
    await expect(bookBtn).not.toBeDisabled();

    // Verify required fields exist
    await expect(page.locator('#donorName')).toHaveAttribute('required', '');
    await expect(page.locator('#donorEmail')).toHaveAttribute('required', '');
    await expect(page.locator('#donorPhone')).toHaveAttribute('required', '');
    await expect(page.locator('#pujaDate')).toHaveAttribute('required', '');
  });

  test('booking form accepts valid input', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Select offering
    await page.locator('#btn-g1').click();

    // Fill form
    await page.fill('#donorName', 'Test Devotee');
    await page.fill('#gotra', 'Kasyapa');
    await page.fill('#occasion', 'Birthday blessings');
    await page.fill('#donorEmail', 'test@iskcon.com');
    await page.fill('#donorPhone', '+91-9876543210');

    // Set date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const dateStr = tomorrow.toISOString().split('T')[0];
    await page.fill('#pujaDate', dateStr);

    // Verify form values
    await expect(page.locator('#donorName')).toHaveValue('Test Devotee');
    await expect(page.locator('#gotra')).toHaveValue('Kasyapa');
    await expect(page.locator('#donorEmail')).toHaveValue('test@iskcon.com');
  });

  test('puja date minimum is today', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    const minDate = await page.locator('#pujaDate').getAttribute('min');
    const today = new Date().toISOString().split('T')[0];
    expect(minDate).toBe(today);
  });

  test('all puja detail pages load without errors', async ({ page }) => {
    const slugs = [
      'sri-sri-radha-madhav',
      'sri-sri-gaura-nitai',
      'sri-giriraja-sila',
      'sri-saligrama-sila',
      'guru-puja',
      'anniversary',
      'birthday',
    ];

    for (const slug of slugs) {
      const response = await page.goto(`/booking/puja/${slug}`);
      expect(response.status()).toBe(200);
      await expect(page.locator('h1')).not.toBeEmpty();
    }
  });

  test('back to puja listing navigation works', async ({ page }) => {
    await page.goto('/booking/puja/sri-sri-radha-madhav');

    // Click breadcrumb "Puja Offerings" link
    await page.locator('.breadcrumb a:has-text("Puja Offerings")').click();
    await expect(page).toHaveURL(/\/booking\/puja\/?$/);
  });

  test('invalid puja slug redirects to listing', async ({ page }) => {
    const response = await page.goto('/booking/puja/invalid-puja-slug');
    // Should redirect to puja listing
    await expect(page).toHaveURL(/\/booking\/puja\/?$/);
  });
});
