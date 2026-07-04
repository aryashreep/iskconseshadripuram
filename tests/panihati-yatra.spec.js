const { test, expect } = require('@playwright/test');

test.describe('Panihati Yatra Registration Flow', () => {

  test('registration page loads correctly', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await expect(page).toHaveTitle(/Panihati/i);
    await expect(page.locator('h1')).toContainText('Panihati');

    // Banner image visible
    await expect(page.locator('img[alt="Panihati Yatra Banner"]')).toBeVisible();

    // About section
    await expect(page.locator('h2:has-text("About the Event")')).toBeVisible();

    // Venue section
    await expect(page.locator('h3:has-text("Venue Details")')).toBeVisible();
    await expect(page.locator('text=Dodda Gosai Ghat')).toBeVisible();
  });

  test('registration form renders all fields', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Form fields
    await expect(page.locator('#regName')).toBeVisible();
    await expect(page.locator('#regPhone')).toBeVisible();
    await expect(page.locator('#regEmail')).toBeVisible();

    // Travel mode radio buttons
    await expect(page.locator('input[value="bus"]')).toBeChecked();
    await expect(page.locator('input[value="own_vehicle"]')).not.toBeChecked();

    // Counter fields
    await expect(page.locator('#cntAdults')).toHaveValue('1');
    await expect(page.locator('#cntKids')).toHaveValue('0');

    // Dropdowns
    await expect(page.locator('#selBhaktiSadan')).toBeVisible();
    await expect(page.locator('#selPickup')).toBeVisible();

    // Submit button
    await expect(page.locator('#btnSubmit')).toContainText('Register & Pay');
  });

  test('bus travel mode is selected by default', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await expect(page.locator('#labelBus')).toHaveClass(/travel-mode-active/);
    await expect(page.locator('#labelVehicle')).not.toHaveClass(/travel-mode-active/);

    // Pickup dropdown should be enabled
    await expect(page.locator('#selPickup')).not.toBeDisabled();
  });

  test('switching to own vehicle disables pickup dropdown', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Switch to own vehicle
    await page.locator('#labelVehicle').click();

    await expect(page.locator('#labelVehicle')).toHaveClass(/travel-mode-active/);
    await expect(page.locator('#labelBus')).not.toHaveClass(/travel-mode-active/);

    // Pickup dropdown should be disabled
    await expect(page.locator('#selPickup')).toBeDisabled();
  });

  test('price calculation updates with adult count', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Default: 1 adult × ₹1,000 = ₹1,000
    await expect(page.locator('#valTotalSummary')).toContainText('₹1,000');
    await expect(page.locator('#btnAmount')).toContainText('₹1,000');

    // Change to 3 adults
    await page.fill('#cntAdults', '3');
    await page.locator('#cntAdults').dispatchEvent('change');

    // 3 × ₹1,000 = ₹3,000
    await expect(page.locator('#valTotalSummary')).toContainText('₹3,000');
    await expect(page.locator('#btnAmount')).toContainText('₹3,000');
  });

  test('price calculation updates with kids count', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Add 2 kids
    await page.fill('#cntKids', '2');
    await page.locator('#cntKids').dispatchEvent('change');

    // 1 adult × ₹1,000 + 2 kids × ₹600 = ₹2,200
    await expect(page.locator('#valTotalSummary')).toContainText('₹2,200');
  });

  test('own vehicle pricing is different from bus', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Bus: 1 adult × ₹1,000 = ₹1,000
    await expect(page.locator('#valTotalSummary')).toContainText('₹1,000');

    // Switch to own vehicle
    await page.locator('#labelVehicle').click();

    // Own vehicle: 1 adult × ₹600 = ₹600
    await expect(page.locator('#valTotalSummary')).toContainText('₹600');
  });

  test('price summary labels update correctly', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await page.fill('#cntAdults', '2');
    await page.locator('#cntAdults').dispatchEvent('change');
    await page.fill('#cntKids', '1');
    await page.locator('#cntKids').dispatchEvent('change');

    // Labels should reflect counts (JS uses toLocaleString which may not add commas)
    await expect(page.locator('#lblAdultSummary')).toContainText('Adults (2 ×');
    await expect(page.locator('#lblKidsSummary')).toContainText('Kids (1 ×');
  });

  test('bhakti sadan dropdown has options', async ({ page }) => {
    await page.goto('/yatra/panihati');

    const options = page.locator('#selBhaktiSadan option');
    const count = await options.count();
    // Should have placeholder + many sadans
    expect(count).toBeGreaterThan(20);
  });

  test('pickup location dropdown has options', async ({ page }) => {
    await page.goto('/yatra/panihati');

    const options = page.locator('#selPickup option');
    const count = await options.count();
    expect(count).toBeGreaterThan(40);
  });

  test('form requires all mandatory fields', async ({ page }) => {
    await page.goto('/yatra/panihati');

    // Verify required attributes
    await expect(page.locator('#regName')).toHaveAttribute('required', '');
    await expect(page.locator('#regPhone')).toHaveAttribute('required', '');
    await expect(page.locator('#regEmail')).toHaveAttribute('required', '');
    await expect(page.locator('#selBhaktiSadan')).toHaveAttribute('required', '');
    await expect(page.locator('#selPickup')).toHaveAttribute('required', '');
  });

  test('gallery images are visible', async ({ page }) => {
    await page.goto('/yatra/panihati');

    const galleryImages = page.locator('.gallery-item img');
    await expect(galleryImages).toHaveCount(2);
    await expect(galleryImages.first()).toBeVisible();
    await expect(galleryImages.last()).toBeVisible();
  });

  test('venue info displays correctly', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await expect(page.locator('text=Dodda Gosai Ghat, Srirangapatna')).toBeVisible();
    await expect(page.locator('text=Kaveri River')).toBeVisible();
    await expect(page.locator('text=8:30 AM')).toBeVisible();
  });

  test('kids under 5 free notice is displayed', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await expect(page.locator('text=Children under 5 years')).toBeVisible();
    await expect(page.locator('text=Free')).toBeVisible();
  });

  test('razorpay security notice is shown', async ({ page }) => {
    await page.goto('/yatra/panihati');

    await expect(page.locator('text=Secured by')).toBeVisible();
    await expect(page.locator('text=Razorpay')).toBeVisible();
  });
});
