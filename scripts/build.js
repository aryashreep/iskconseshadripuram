#!/usr/bin/env node

/**
 * ISJM Build Script
 * 
 * Minifies JS/CSS and generates content-hashed filenames for cache busting.
 * Outputs a manifest.json mapping original → hashed filenames.
 * 
 * Usage:
 *   npm run build          # Build all
 *   npm run build:js       # JS only
 *   npm run build:css      # CSS only
 *   npm run build -- --watch  # Watch mode
 *   npm run build -- --clean  # Clean dist/
 */

const fs = require("fs");
const path = require("path");
const crypto = require("crypto");

// Paths
const ROOT = path.resolve(__dirname, "..");
const SRC_JS = path.join(ROOT, "assets", "js");
const SRC_CSS = path.join(ROOT, "assets", "css");
const DIST = path.join(ROOT, "assets", "dist");
const MANIFEST_PATH = path.join(DIST, "manifest.json");

// Files to process
const JS_FILES = [
  "cart.js",
  "donate.js",
  "checkout.js",
  "puja-detail.js",
  "main.js",
];

const CSS_FILES = [
  "style.css",
  "responsive.css",
  "donate.css",
  "admin.css",
  // Page-specific CSS (extracted from inline <style> blocks)
  "pages/admin/donations.css",
  "pages/booking/guest-house.css",
  "pages/booking-index.css",
  "pages/booking/puja-detail.css",
  "pages/booking/puja-index.css",
  "pages/booking/yagya-detail.css",
  "pages/booking/yagya-index.css",
  "pages/checkout.css",
  "pages/contact.css",
  "pages/darshan.css",
  "pages/festivals/vaishnava-calendar.css",
  "pages/services/siksha.css",
  "pages/yatra/panihati.css",
];

// Parse args
const args = process.argv.slice(2);
const jsOnly = args.includes("--js-only");
const cssOnly = args.includes("--css-only");
const watchMode = args.includes("--watch");
const cleanMode = args.includes("--clean");

// Ensure dist directory exists
function ensureDir(dir) {
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }
}

// Generate content hash (first 8 chars of SHA-256)
function contentHash(content) {
  return crypto.createHash("sha256").update(content).digest("hex").slice(0, 8);
}

// Minify JS using terser
async function minifyJS(filePath) {
  const { minify } = require("terser");
  const code = fs.readFileSync(filePath, "utf8");
  const result = await minify(code, {
    compress: {
      drop_console: false, // Keep console.log for debugging
      passes: 2,
    },
    mangle: true,
    output: {
      comments: false,
    },
  });
  return result.code;
}

// Minify CSS using lightningcss
function minifyCSS(filePath) {
  const { transform } = require("lightningcss");
  const code = fs.readFileSync(filePath, "utf8");
  const result = transform({
    code: Buffer.from(code),
    minify: true,
    filename: path.basename(filePath),
  });
  return result.code.toString();
}

// Build a single file
async function buildFile(srcDir, fileName, minifyFn, subDir = "js") {
  const srcPath = path.join(srcDir, fileName);
  
  if (!fs.existsSync(srcPath)) {
    console.log(`  ⏭  ${fileName} (not found, skipping)`);
    return null;
  }

  const content = fs.readFileSync(srcPath, "utf8");
  const hash = contentHash(content);
  const ext = path.extname(fileName);
  const base = path.basename(fileName, ext);
  const hashedName = `${base}.${hash}${ext}`;
  const distPath = path.join(DIST, hashedName);

  const minified = await minifyFn(srcPath);
  fs.writeFileSync(distPath, minified, "utf8");

  const originalSize = Buffer.byteLength(content);
  const minifiedSize = Buffer.byteLength(minified);
  const savings = Math.round((1 - minifiedSize / originalSize) * 100);

  console.log(`  ✅ ${fileName} → ${hashedName} (${savings}% smaller)`);

  return {
    original: `assets/${subDir}/${fileName}`,
    built: `assets/dist/${hashedName}`,
    hash,
  };
}

// Build all files
async function build() {
  console.log("\n🔨 ISJM Build\n");
  ensureDir(DIST);

  const manifest = {};

  // Build JS
  if (!cssOnly) {
    console.log("📦 JavaScript:");
    for (const file of JS_FILES) {
      const result = await buildFile(SRC_JS, file, minifyJS);
      if (result) {
        manifest[result.original] = result.built;
      }
    }
  }

  // Build CSS
  if (!jsOnly) {
    console.log("\n🎨 CSS:");
    for (const file of CSS_FILES) {
      const result = await buildFile(SRC_CSS, file, minifyCSS, "css");
      if (result) {
        manifest[result.original] = result.built;
      }
    }
  }

  // Write manifest
  fs.writeFileSync(MANIFEST_PATH, JSON.stringify(manifest, null, 2), "utf8");
  console.log(`\n📋 Manifest: ${MANIFEST_PATH}`);
  console.log("\n✅ Build complete!\n");
}

// Clean dist directory
function clean() {
  if (fs.existsSync(DIST)) {
    fs.rmSync(DIST, { recursive: true });
    console.log("🧹 Cleaned assets/dist/");
  } else {
    console.log("Nothing to clean.");
  }
}

// Main
if (cleanMode) {
  clean();
} else {
  build().catch((err) => {
    console.error("❌ Build failed:", err);
    process.exit(1);
  });
}
