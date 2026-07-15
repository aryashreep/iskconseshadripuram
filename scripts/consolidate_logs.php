<?php
/**
 * Script to consolidate modules/logs into root logs/ directory.
 */

$src = __DIR__ . '/../modules/logs';
$dst = __DIR__ . '/../logs';

if (is_dir($src)) {
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $srcFile = $src . '/' . $file;
        $dstFile = $dst . '/' . $file;
        
        if (file_exists($dstFile)) {
            // Append contents if it already exists
            file_put_contents($dstFile, file_get_contents($srcFile), FILE_APPEND);
            unlink($srcFile);
        } else {
            // Rename/move
            rename($srcFile, $dstFile);
        }
    }
    rmdir($src);
    echo "[OK] Logs consolidated from modules/logs to root logs/ directory.\n";
} else {
    echo "[INFO] No modules/logs directory to consolidate.\n";
}
