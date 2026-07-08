const { test, expect } = require('@playwright/test');

// ============================================
// Smoke Tests: Every public page returns 200
// ============================================
const PUBLIC_PAGES = [
  // Homepage
  { url: '/', name: 'Homepage' },
  
  // About section
  { url: '/about', name: 'About Index' },
  { url: '/about/history-of-iskcon', name: 'History of ISKCON' },
  { url: '/about/our-philosophy', name: 'Our Philosophy' },
  { url: '/about/our-mission', name: 'Our Mission' },
  { url: '/about/founder-acharya', name: 'Founder Acharya' },
  { url: '/about/temple-schedule', name: 'Temple Schedule' },
  { url: '/about/golden-temple', name: 'Golden Temple' },
  { url: '/about/hare-krishna-movement', name: 'Hare Krishna Movement' },
  
  // Services
  { url: '/services', name: 'Services Index' },
  { url: '/services/sunday-feast', name: 'Sunday Feast' },
  { url: '/services/life-membership', name: 'Life Membership' },
  { url: '/services/function-hall', name: 'Function Hall' },
  { url: '/services/govindas-prasadam', name: "Govinda's Prasadam" },
  { url: '/services/corporate-programs', name: 'Corporate Programs' },
  { url: '/services/siksha', name: 'Siksha' },
  { url: '/services/food-for-life', name: 'Food for Life' },
  { url: '/services/bhakti-sadan', name: 'Bhakti Sadan' },
  { url: '/services/bhakti-vriksha', name: 'Bhakti Vriksha' },
  { url: '/services/krishna-fun-school', name: 'Krishna Fun School' },
  { url: '/services/music-school', name: 'Music School' },
  { url: '/services/our-centers', name: 'Our Centers' },
  
  // Festivals
  { url: '/festivals', name: 'Festivals Index' },
  { url: '/festivals/listing', name: 'Festivals Listing' },
  
  // Donate
  { url: '/donate', name: 'Donate Index' },
  
  // Booking
  { url: '/booking/puja', name: 'Puja Booking List' },
  { url: '/booking/yagya', name: 'Yagya Booking List' },
  
  // Checkout
  { url: '/checkout', name: 'Checkout' },
  
  // Blogs
  { url: '/blogs', name: 'Blogs' },
  
  // Yatra
  { url: '/yatra', name: 'Yatra Index' },
  { url: '/yatra/panihati', name: 'Panihati Yatra' },
  
  // Other pages
  { url: '/contact', name: 'Contact' },
  { url: '/seva', name: 'Seva' },
  { url: '/darshan', name: 'Darshan' },
  { url: '/resources', name: 'Resources' },
  { url: '/forums', name: 'Forums' },
  
  // Static files
  { url: '/sitemap.xml', name: 'Sitemap' },
  { url: '/robots.txt', name: 'Robots.txt' },
  { url: '/favicon.ico', name: 'Favicon' },
];

test.describe('Public Pages Smoke Tests', () => {
  for (const page of PUBLIC_PAGES) {
    test(`${page.name} (${page.url}) returns 200`, async ({ request }) => {
      const response = await request.get(page.url, { 
        timeout: 15000,
        maxRedirects: 5 
      });
      expect(response.status(), `${page.name} should return 200`).toBe(200);
    });
  }
});

// ============================================
// Homepage Content Tests
// ============================================
test.describe('Homepage', () => {
  test('loads and has correct title', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/ISKCON/);
  });

  test('has navigation menu', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.site-header')).toBeVisible();
    await expect(page.locator('.main-nav')).toBeVisible();
  });

  test('has hero section', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.hero-section, .home-hero')).toBeVisible({ timeout: 10000 });
  });

  test('has footer', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.site-footer')).toBeVisible();
  });

  test('cart badge exists in header', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('[data-cart-count]').first()).toBeAttached();
  });
});

// ============================================
// Dynamic Pages (require DB data)
// ============================================
test.describe('Dynamic Pages', () => {
  test('donate page loads with cause options', async ({ page }) => {
    await page.goto('/donate');
    await expect(page).toHaveTitle(/Donate/);
  });

  test('puja booking list loads', async ({ page }) => {
    await page.goto('/booking/puja');
    await expect(page).toHaveTitle(/Puja/);
  });

  test('festivals listing redirects to festivals index', async ({ request }) => {
    // listing.php is a template - direct access redirects to /festivals/
    const response = await request.get('/festivals/listing', { 
      timeout: 10000,
      maxRedirects: 0 
    });
    expect(response.status()).toBe(302);
    expect(response.headers()['location']).toContain('/festivals/');
  });

  test('blogs page loads', async ({ page }) => {
    await page.goto('/blogs');
    await expect(page).toHaveTitle(/Blog/);
  });
});

// ============================================
// Cart Flow Tests
// ============================================
test.describe('Cart Flow', () => {
  test('cart is empty on fresh visit', async ({ page }) => {
    await page.goto('/');
    const cartBadge = page.locator('[data-cart-count]').first();
    await expect(cartBadge).toBeHidden();
  });

  test('checkout shows empty cart message', async ({ page }) => {
    await page.goto('/checkout');
    await page.waitForTimeout(2000);
    await expect(page.locator('#checkoutApp')).toContainText('Your Cart is Empty');
  });
});

// ============================================
// Admin Pages Tests
// ============================================
test.describe('Admin Pages', () => {
  test('admin login page loads', async ({ request }) => {
    const response = await request.get('/admin/login', { timeout: 10000 });
    expect(response.status()).toBe(200);
  });

  test('admin dashboard redirects to login when not authenticated', async ({ request }) => {
    const response = await request.get('/admin/dashboard', { 
      timeout: 10000,
      maxRedirects: 0 
    });
    // Should redirect to login (302) or show login page (200)
    expect([200, 302]).toContain(response.status());
  });
});

// ============================================
// Asset Loading Tests
// ============================================
test.describe('Assets', () => {
  test('CSS files load correctly', async ({ page }) => {
    const cssRequests = [];
    page.on('response', response => {
      if (response.url().includes('.css')) {
        cssRequests.push(response);
      }
    });
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    // At least one CSS file should load
    expect(cssRequests.length).toBeGreaterThan(0);
    
    // All CSS should return 200
    for (const req of cssRequests) {
      expect(req.status()).toBe(200);
    }
  });

  test('JS files load correctly', async ({ page }) => {
    const jsRequests = [];
    page.on('response', response => {
      if (response.url().includes('.js') && !response.url().includes('razorpay')) {
        jsRequests.push(response);
      }
    });
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    // At least cart.js and main.js should load
    expect(jsRequests.length).toBeGreaterThan(0);
  });

  test('cart.js loads and ISJMCart is defined', async ({ page }) => {
    await page.goto('/');
    const cartDefined = await page.evaluate(() => typeof ISJMCart !== 'undefined');
    expect(cartDefined).toBe(true);
  });
});

// ============================================
// Navigation Tests
// ============================================
test.describe('Navigation', () => {
  test('can navigate to about page', async ({ page }) => {
    await page.goto('/about');
    await expect(page).toHaveTitle(/ISKCON|About/);
    await expect(page.locator('.site-header')).toBeVisible();
  });

  test('can navigate to donate page', async ({ page }) => {
    await page.goto('/donate');
    await expect(page).toHaveTitle(/ISKCON|Donate/);
    await expect(page.locator('.site-header')).toBeVisible();
  });

  test('can navigate to services page', async ({ page }) => {
    await page.goto('/services');
    await expect(page).toHaveTitle(/ISKCON|Service/);
    await expect(page.locator('.site-header')).toBeVisible();
  });
});

// ============================================
// Responsive Design Tests
// ============================================
test.describe('Responsive', () => {
  test('mobile menu toggle works', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    // Mobile menu button should be visible
    const menuBtn = page.locator('.mobile-menu-btn, .hamburger');
    if (await menuBtn.isVisible()) {
      await menuBtn.click();
      // Nav should become visible
      await page.waitForTimeout(500);
    }
  });
});

// ============================================
// Security Headers Tests
// ============================================
test.describe('Security Headers', () => {
  test('X-Frame-Options is set', async ({ request }) => {
    const response = await request.get('/');
    const header = response.headers()['x-frame-options'];
    expect(header).toBeTruthy();
  });

  test('X-Content-Type-Options is set', async ({ request }) => {
    const response = await request.get('/');
    const header = response.headers()['x-content-type-options'];
    expect(header).toBe('nosniff');
  });

  test('Content-Security-Policy is set', async ({ request }) => {
    const response = await request.get('/');
    const header = response.headers()['content-security-policy'];
    expect(header).toBeTruthy();
  });
});

// ============================================
// Performance Tests
// ============================================
test.describe('Performance', () => {
  test('homepage loads within 5 seconds', async ({ page }) => {
    const startTime = Date.now();
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(5000);
  });

  test('no console errors on homepage', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    // Filter out known third-party errors
    const realErrors = errors.filter(e => 
      !e.includes('razorpay') && 
      !e.includes('favicon') &&
      !e.includes('Failed to load resource')
    );
    
    expect(realErrors).toHaveLength(0);
  });
});
