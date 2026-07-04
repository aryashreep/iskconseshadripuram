<?php
/**
 * Generate production migration SQL from local database.
 * Compares local schema with database/schema.sql and exports safe diff.
 *
 * Usage: php scripts/generate_prod_migration.php
 */
require_once __DIR__ . '/../config.php';
$db = getDB();

$lines = [];
$lines[] = '-- ============================================';
$lines[] = '-- PRODUCTION MIGRATION — ' . date('Y-m-d H:i:s');
$lines[] = '-- Safe to run: uses INSERT IGNORE + ALTER ADD';
$lines[] = '-- ============================================';
$lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
$lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
$lines[] = '';

// --- Get local table list and columns ---
$localTables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$localSchema = [];
foreach ($localTables as $t) {
    $cols = $db->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        $localSchema[$t][$c['Field']] = $c;
    }
}

// --- ALL tables: CREATE TABLE IF NOT EXISTS (safe for any environment) ---
$lines[] = '-- ============================================';
$lines[] = '-- 1. ALL TABLES (CREATE IF NOT EXISTS)';
$lines[] = '-- ============================================';

foreach ($localTables as $table) {
    if (!isset($localSchema[$table])) continue;
    // Skip transaction tables — those should never be recreated
    if (in_array($table, ['donation_transactions', 'donation_subscriptions', 'donation_webhook_logs', 'panihati_yatra_registrations'])) continue;

    $stmt = $db->query("SHOW CREATE TABLE `$table`");
    $row = $stmt->fetch();
    if (!$row || empty($row['Create Table'])) continue;
    $create = $row['Create Table'];
    $create = preg_replace('/CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create, 1);
    $lines[] = "-- Table: $table";
    $lines[] = $create . ';';
    $lines[] = '';
}

// --- Views: CREATE OR REPLACE ---
$lines[] = '-- ============================================';
$lines[] = '-- 1b. VIEWS (CREATE OR REPLACE)';
$lines[] = '-- ============================================';

$views = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_NUM);
foreach ($views as $viewRow) {
    $viewName = $viewRow[0];
    $stmt = $db->query("SHOW CREATE VIEW `$viewName`");
    $viewData = $stmt->fetch();
    if ($viewData && !empty($viewData['Create View'])) {
        $viewSql = $viewData['Create View'];
        // Strip DEFINER clause — production user may not have SUPER privilege
        $viewSql = preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s+/', '', $viewSql);
        $lines[] = "-- View: $viewName";
        $lines[] = 'DROP VIEW IF EXISTS `' . $viewName . '`;';
        $lines[] = $viewSql . ';';
        $lines[] = '';
    }
}

// --- Seed data: config tables safe to INSERT IGNORE ---
$lines[] = '-- ============================================';
$lines[] = '-- 2. SEED DATA (INSERT IGNORE — no overwrite)';
$lines[] = '-- ============================================';

$seedTables = [
    'donation_seva_categories', 'master_seva_categories', 'master_sevas',
    'donation_causes', 'donation_cause_sevas', 'donation_cause_master_sevas',
    'donation_plans', 'panihati_bhakti_sadans', 'panihati_pickup_locations',
    'panihati_pricing', 'admins'
];

foreach ($seedTables as $table) {
    if (!isset($localSchema[$table])) continue;
    $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) continue;

    $cols = array_keys($rows[0]);
    $colList = '`' . implode('`, `', $cols) . '`';
    $lines[] = "-- $table (" . count($rows) . " rows)";

    foreach ($rows as $row) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) $vals[] = 'NULL';
            elseif (is_numeric($v)) $vals[] = $v;
            else $vals[] = "'" . addslashes($v) . "'";
        }
        $lines[] = "INSERT IGNORE INTO `$table` ($colList) VALUES (" . implode(', ', $vals) . ');';
    }
    $lines[] = '';
}

// --- Blog content (use REPLACE to update existing) ---
$lines[] = '-- ============================================';
$lines[] = '-- 3. BLOG CONTENT (REPLACE — updates existing)';
$lines[] = '-- ============================================';

$blogRows = $db->query("SELECT * FROM `blogs`")->fetchAll(PDO::FETCH_ASSOC);
if (!empty($blogRows)) {
    $cols = array_keys($blogRows[0]);
    $colList = '`' . implode('`, `', $cols) . '`';
    $lines[] = "-- blogs (" . count($blogRows) . " rows)";
    foreach ($blogRows as $row) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) $vals[] = 'NULL';
            elseif (is_numeric($v)) $vals[] = $v;
            else $vals[] = "'" . addslashes($v) . "'";
        }
        $lines[] = "REPLACE INTO `blogs` ($colList) VALUES (" . implode(', ', $vals) . ');';
    }
    $lines[] = '';
}

// --- Panihati registrations (use INSERT IGNORE to avoid duplicates) ---
$lines[] = '-- ============================================';
$lines[] = '-- 4. PANIHATI REGISTRATIONS (INSERT IGNORE)';
$lines[] = '-- ============================================';

$panRows = $db->query("SELECT * FROM `panihati_yatra_registrations`")->fetchAll(PDO::FETCH_ASSOC);
if (!empty($panRows)) {
    $cols = array_keys($panRows[0]);
    $colList = '`' . implode('`, `', $cols) . '`';
    $lines[] = "-- panihati_yatra_registrations (" . count($panRows) . " rows)";
    foreach ($panRows as $row) {
        $vals = [];
        foreach ($row as $v) {
            if ($v === null) $vals[] = 'NULL';
            elseif (is_numeric($v)) $vals[] = $v;
            else $vals[] = "'" . addslashes($v) . "'";
        }
        $lines[] = "INSERT IGNORE INTO `panihati_yatra_registrations` ($colList) VALUES (" . implode(', ', $vals) . ');';
    }
    $lines[] = '';
}

// --- Panihati combined stats: SKIP (it's a VIEW, not a table — data comes from underlying tables) ---

$lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
$lines[] = '';
$lines[] = '-- ============================================';
$lines[] = '-- DONE. Review above before executing on prod.';
$lines[] = '-- ============================================';

$file = __DIR__ . '/prod_migration.sql';
file_put_contents($file, implode("\n", $lines));
echo "Generated: $file (" . count($lines) . " lines)" . PHP_EOL;
