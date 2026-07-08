<?php
/**
 * Asset Helper - Cache-busted asset URLs
 * 
 * Reads manifest.json to map original filenames to content-hashed versions.
 * Falls back to original filenames if manifest doesn't exist.
 * 
 * Usage:
 *   <script src="<?= asset('assets/js/cart.js') ?>"></script>
 *   <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
 */

/**
 * Get the cache-busted URL for an asset.
 * 
 * @param string $path Original asset path (e.g., 'assets/js/cart.js')
 * @return string Path to the built asset (or original if not in manifest)
 */
function asset(string $path): string
{
    static $manifest = null;
    static $baseUrl = null;

    // Load manifest once
    if ($manifest === null) {
        $manifestPath = dirname(__DIR__, 2) . '/assets/dist/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        } else {
            $manifest = [];
        }
    }

    // Base URL
    if ($baseUrl === null) {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/';
    }

    // Normalize path (remove leading slash)
    $path = ltrim($path, '/');

    // Return hashed version if available
    if (isset($manifest[$path])) {
        return $baseUrl . $manifest[$path];
    }

    // Fallback to original path
    return $baseUrl . $path;
}

/**
 * Get the cache-busted path for use in PHP includes.
 * 
 * @param string $path Original asset path
 * @return string Absolute filesystem path to the built asset
 */
function assetPath(string $path): string
{
    static $manifest = null;

    if ($manifest === null) {
        $manifestPath = dirname(__DIR__) . '/assets/dist/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
        } else {
            $manifest = [];
        }
    }

    $path = ltrim($path, '/');

    if (isset($manifest[$path])) {
        return dirname(__DIR__, 2) . '/' . $manifest[$path];
    }

    return dirname(__DIR__, 2) . '/' . $path;
}
