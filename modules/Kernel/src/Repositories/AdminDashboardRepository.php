<?php

namespace Isjm\Repositories;

use PDO;
use PDOException;

/**
 * Repository for the consolidated admin dashboard.
 *
 * Aggregates revenue data across all modules:
 *   - General Donations (donation_transactions not linked to bookings)
 *   - Puja / Yagya (booking_pujas JOIN donation_transactions)
 *   - Panihati Yatra (panihati_yatra_registrations)
 *   - Sudamaseva (sudamaseva_payments)
 *
 * Avoids double-counting by excluding booking-linked transactions
 * from the general donations total.
 */
class AdminDashboardRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDB();
    }

    // ============================================================
    // UNIFIED REVENUE EVENTS (normalized UNION ALL across modules)
    // ============================================================

    /**
     * Build the base UNION ALL query that normalises every paid revenue
     * event into a common row signature.
     *
     * Columns returned:
     *   module       — slug: 'donations', 'puja', 'yagya', 'panihati', 'sudamaseva'
     *   source_id    — PK in the source table
     *   source_label — human-readable source description
     *   donor_name   — contributor name
     *   donor_phone  — contributor phone
     *   amount       — DECIMAL(12,2)
     *   paid_at      — DATETIME when the revenue was collected
     */
    private function getRevenueUnionSql(): string
    {
        return "
            -- 1. General Donation Transactions (exclude booking-linked)
            SELECT
                'donations' AS module,
                t.id AS source_id,
                COALESCE(c.title, 'General Donation') AS source_label,
                t.donor_name,
                t.donor_phone,
                CAST(t.amount AS DECIMAL(12,2)) AS amount,
                t.created_at AS paid_at
            FROM donation_transactions t
            LEFT JOIN donation_causes c ON c.id = t.cause_id
            WHERE t.payment_status = 'paid'
              AND t.id NOT IN (SELECT transaction_id FROM booking_pujas WHERE transaction_id IS NOT NULL)

            UNION ALL

            -- 2. Puja / Yagya Bookings (linked to donation_transactions)
            SELECT
                CASE WHEN LOWER(b.puja_type) LIKE '%yagya%' THEN 'yagya' ELSE 'puja' END AS module,
                b.id AS source_id,
                b.puja_type AS source_label,
                b.person_name AS donor_name,
                NULL AS donor_phone,
                CAST(t.amount AS DECIMAL(12,2)) AS amount,
                t.created_at AS paid_at
            FROM booking_pujas b
            JOIN donation_transactions t ON t.id = b.transaction_id
            WHERE t.payment_status = 'paid'

            UNION ALL

            -- 3. Panihati Yatra Registrations
            SELECT
                'panihati' AS module,
                p.id AS source_id,
                CONCAT('Yatra - ', p.travel_mode) AS source_label,
                p.name AS donor_name,
                p.phone AS donor_phone,
                CAST(p.amount AS DECIMAL(12,2)) AS amount,
                p.created_at AS paid_at
            FROM panihati_yatra_registrations p
            WHERE p.payment_status IN ('paid', 'offline')

            UNION ALL

            -- 4. Sudamaseva Payments
            SELECT
                'sudamaseva' AS module,
                sp.id AS source_id,
                CONCAT('Installment #', sp.installment_number) AS source_label,
                d.donor_name,
                d.phone AS donor_phone,
                CAST(sp.amount AS DECIMAL(12,2)) AS amount,
                sp.payment_date AS paid_at
            FROM sudamaseva_payments sp
            LEFT JOIN sudamaseva_donors d ON d.id = sp.donor_id
            WHERE sp.payment_status = 'paid'
        ";
    }

    // ============================================================
    // AGGREGATE QUERIES
    // ============================================================

    /**
     * Build a WHERE clause for date filtering.
     *
     * @param string $alias Table alias for paid_at column
     * @param string|null $from Start date (Y-m-d), inclusive
     * @param string|null $to   End date (Y-m-d), inclusive
     * @return string SQL fragment starting with AND (or empty)
     */
    private function dateFilter(string $alias, ?string $from, ?string $to): string
    {
        $parts = [];
        if (!empty($from)) {
            $fromEsc = $this->db->quote($from . ' 00:00:00');
            $parts[] = "{$alias}.paid_at >= {$fromEsc}";
        }
        if (!empty($to)) {
            $toEsc = $this->db->quote($to . ' 23:59:59');
            $parts[] = "{$alias}.paid_at <= {$toEsc}";
        }
        if (empty($parts)) {
            return '';
        }
        return ' AND ' . implode(' AND ', $parts);
    }

    /**
     * Get overall revenue KPIs for the current financial period.
     *
     * @param array       $visibleModules  Modules the user can see (empty = all)
     * @param string|null $from            Start date filter (Y-m-d)
     * @param string|null $to              End date filter (Y-m-d)
     * @return array Keys: total_collections, this_month, today, total_entries
     */
    public function getOverview(array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        try {
            $revenueSql = $this->getRevenueUnionSql();
            $moduleFilter = $this->moduleFilter('revenue', $visibleModules);
            $dateFilter = $this->dateFilter('revenue', $from, $to);
            $whereClause = $moduleFilter . $dateFilter;

            // Total collections
            $sql = "SELECT COALESCE(SUM(amount), 0) FROM ({$revenueSql}) revenue WHERE 1=1 {$whereClause}";
            $total = (float) $this->db->query($sql)->fetchColumn();

            // This month (always current month, unaffected by date filter)
            if (empty($from) && empty($to)) {
                $sql = "SELECT COALESCE(SUM(amount), 0) FROM ({$revenueSql}) revenue
                        WHERE paid_at >= DATE_FORMAT(NOW(), '%Y-%m-01') {$moduleFilter}";
                $thisMonth = (float) $this->db->query($sql)->fetchColumn();
            } else {
                $thisMonth = $total; // When filtering, "this month" = total within filter
            }

            // Today
            $sql = "SELECT COALESCE(SUM(amount), 0) FROM ({$revenueSql}) revenue
                    WHERE DATE(paid_at) = CURDATE() {$moduleFilter}";
            $today = (float) $this->db->query($sql)->fetchColumn();

            // Total entries within filter
            $sql = "SELECT COUNT(*) FROM ({$revenueSql}) revenue WHERE 1=1 {$whereClause}";
            $totalEntries = (int) $this->db->query($sql)->fetchColumn();

            return [
                'total_collections' => $total,
                'this_month' => $thisMonth,
                'today' => $today,
                'total_entries' => $totalEntries,
            ];
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getOverview error: ' . $e->getMessage());
            return ['total_collections' => 0, 'this_month' => 0, 'today' => 0, 'total_entries' => 0];
        }
    }

    /**
     * Get per-module revenue breakdown.
     *
     * @param array       $visibleModules
     * @param string|null $from            Start date filter (Y-m-d)
     * @param string|null $to              End date filter (Y-m-d)
     * @return array Each row: module, total_amount, payment_count, this_month_amount
     */
    public function getModuleBreakdown(array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        try {
            $revenueSql = $this->getRevenueUnionSql();
            $moduleFilter = $this->moduleFilter('revenue', $visibleModules);
            $dateFilter = $this->dateFilter('revenue', $from, $to);
            $whereClause = $moduleFilter . $dateFilter;

            $sql = "
                SELECT
                    revenue.module,
                    COUNT(*) AS payment_count,
                    SUM(revenue.amount) AS total_amount,
                    SUM(CASE WHEN revenue.paid_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN revenue.amount ELSE 0 END) AS this_month_amount
                FROM ({$revenueSql}) revenue
                WHERE 1=1 {$whereClause}
                GROUP BY revenue.module
                ORDER BY total_amount DESC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getModuleBreakdown error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly revenue trend, grouped by module (for stacked chart).
     *
     * Uses per-module pre-aggregated SELECTs UNIONed together, avoiding
     * a complex derived table that can cause performance/syntax issues
     * in MySQL 8.4 with ONLY_FULL_GROUP_BY.
     *
     * @param int         $months         Number of past months (used only when no date filter)
     * @param array       $visibleModules
     * @param string|null $from            Start date filter (Y-m-d)
     * @param string|null $to              End date filter (Y-m-d)
     * @return array Rows with: yr_month, month_label, module, total_amount
     */
    public function getMonthlyTrend(int $months = 12, array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        try {
            // Build date conditions for each table's date column
            $donationDateCond = $this->buildDateCondition('created_at', $from, $to, $months);
            $bookingDateCond  = $this->buildDateCondition('t.created_at', $from, $to, $months);
            $panihatiDateCond = $this->buildDateCondition('created_at', $from, $to, $months);
            $sudamasevaDateCond = $this->buildDateCondition('payment_date', $from, $to, $months);

            $parts = [];

            // 1. Donations (exclude booking-linked)
            $parts[] = "SELECT DATE_FORMAT(t.created_at, '%Y-%m') AS yr_month, DATE_FORMAT(t.created_at, '%b %Y') AS month_label, 'donations' AS module, SUM(t.amount) AS total_amount FROM donation_transactions t WHERE t.payment_status = 'paid' AND t.id NOT IN (SELECT transaction_id FROM booking_pujas WHERE transaction_id IS NOT NULL) {$donationDateCond} GROUP BY yr_month, month_label";

            // 2. Puja / Yagya
            $parts[] = "SELECT DATE_FORMAT(t.created_at, '%Y-%m') AS yr_month, DATE_FORMAT(t.created_at, '%b %Y') AS month_label, CASE WHEN LOWER(b.puja_type) LIKE '%yagya%' THEN 'yagya' ELSE 'puja' END AS module, SUM(t.amount) AS total_amount FROM booking_pujas b JOIN donation_transactions t ON t.id = b.transaction_id WHERE t.payment_status = 'paid' {$bookingDateCond} GROUP BY yr_month, month_label, module";

            // 3. Panihati Yatra
            $parts[] = "SELECT DATE_FORMAT(p.created_at, '%Y-%m') AS yr_month, DATE_FORMAT(p.created_at, '%b %Y') AS month_label, 'panihati' AS module, SUM(p.amount) AS total_amount FROM panihati_yatra_registrations p WHERE p.payment_status IN ('paid', 'offline') {$panihatiDateCond} GROUP BY yr_month, month_label";

            // 4. Sudamaseva (uses payment_date, not created_at)
            $parts[] = "SELECT DATE_FORMAT(sp.payment_date, '%Y-%m') AS yr_month, DATE_FORMAT(sp.payment_date, '%b %Y') AS month_label, 'sudamaseva' AS module, SUM(sp.amount) AS total_amount FROM sudamaseva_payments sp WHERE sp.payment_status = 'paid' {$sudamasevaDateCond} GROUP BY yr_month, month_label";

            // Combine with UNION ALL
            $unionSql = implode("\nUNION ALL\n", $parts);

            // Apply module filter
            $whereExtra = '';
            if (!empty($visibleModules)) {
                $escaped = array_map(function ($m) { return "'" . trim($m) . "'"; }, $visibleModules);
                $whereExtra = 'AND module IN (' . implode(',', $escaped) . ')';
            }

            $sql = "SELECT * FROM ({$unionSql}) monthly WHERE 1=1 {$whereExtra} ORDER BY yr_month ASC, module ASC";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getMonthlyTrend error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build a date condition for a table query with the given column name.
     * Supports from+to simultaneously (both provided on date-range filter).
     *
     * @param string      $column Column name (may include alias prefix like 't.created_at')
     * @param string|null $from
     * @param string|null $to
     * @param int         $months  Default lookback when no from/to provided
     * @return string SQL fragment starting with AND (or empty)
     */
    private function buildDateCondition(string $column, ?string $from, ?string $to, int $months = 12): string
    {
        $parts = [];
        if (!empty($from)) {
            $parts[] = "{$column} >= " . $this->db->quote($from . ' 00:00:00');
        }
        if (!empty($to)) {
            $parts[] = "{$column} <= " . $this->db->quote($to . ' 23:59:59');
        }
        if (empty($parts)) {
            // No date filter: default to last N months
            return " AND {$column} >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)";
        }
        return ' AND ' . implode(' AND ', $parts);
    }

    /**
     * Get recent collections across all modules (sorted by date DESC).
     *
     * @param int         $limit
     * @param array       $visibleModules
     * @param string|null $from            Start date filter (Y-m-d)
     * @param string|null $to              End date filter (Y-m-d)
     * @return array
     */
    public function getRecentCollections(int $limit = 10, array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        try {
            $revenueSql = $this->getRevenueUnionSql();
            $moduleFilter = $this->moduleFilter('revenue', $visibleModules);
            $dateFilter = $this->dateFilter('revenue', $from, $to);
            $whereClause = $moduleFilter . $dateFilter;

            $sql = "
                SELECT *
                FROM ({$revenueSql}) revenue
                WHERE 1=1 {$whereClause}
                ORDER BY revenue.paid_at DESC
                LIMIT {$limit}
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getRecentCollections error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // RECURRING PIPELINE
    // ============================================================

    /**
     * Get the monthly recurring pipeline (subscriptions, not realized cash).
     *
     * @return array Keys: donation_subs_count, donation_subs_monthly,
     *                      sudamaseva_subs_count, sudamaseva_subs_monthly
     */
    public function getRecurringPipeline(): array
    {
        try {
            // Donation subscriptions
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) AS sub_count,
                    COALESCE(SUM(amount), 0) AS monthly_total
                FROM donation_subscriptions
                WHERE subscription_status = 'active'
            ");
            $donation = $stmt->fetch();

            // Sudamaseva subscriptions
            $stmt = $this->db->query("
                SELECT
                    COUNT(*) AS sub_count,
                    COALESCE(SUM(amount), 0) AS monthly_total
                FROM sudamaseva_subscriptions
                WHERE status = 'active'
            ");
            $sudamaseva = $stmt->fetch();

            return [
                'donation_subs_count' => (int) ($donation['sub_count'] ?? 0),
                'donation_subs_monthly' => (float) ($donation['monthly_total'] ?? 0),
                'sudamaseva_subs_count' => (int) ($sudamaseva['sub_count'] ?? 0),
                'sudamaseva_subs_monthly' => (float) ($sudamaseva['monthly_total'] ?? 0),
                'total_subs_count' => (int) ($donation['sub_count'] ?? 0) + (int) ($sudamaseva['sub_count'] ?? 0),
                'total_monthly' => (float) ($donation['monthly_total'] ?? 0) + (float) ($sudamaseva['monthly_total'] ?? 0),
            ];
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getRecurringPipeline error: ' . $e->getMessage());
            return [
                'donation_subs_count' => 0, 'donation_subs_monthly' => 0,
                'sudamaseva_subs_count' => 0, 'sudamaseva_subs_monthly' => 0,
                'total_subs_count' => 0, 'total_monthly' => 0,
            ];
        }
    }

    // ============================================================
    // MODULE-SPECIFIC EXTRAS
    // ============================================================

    /**
     * Get donation category split (existing chart data).
     */
    public function getDonationCategorySplit(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COALESCE(c.category, 'general') AS category,
                    COUNT(*) AS cnt,
                    SUM(t.amount) AS total
                FROM donation_transactions t
                LEFT JOIN donation_causes c ON t.cause_id = c.id
                WHERE t.payment_status = 'paid'
                  AND t.id NOT IN (SELECT transaction_id FROM booking_pujas WHERE transaction_id IS NOT NULL)
                GROUP BY c.category
                ORDER BY total DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getDonationCategorySplit error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get Panihati expenses total (for net calculation).
     */
    public function getPanihatiExpensesTotal(): float
    {
        try {
            return (float) $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM panihati_expenses")->fetchColumn();
        } catch (PDOException $e) {
            error_log('AdminDashboardRepository::getPanihatiExpensesTotal error: ' . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Build a WHERE clause fragment to filter by visible modules.
     *
     * @param string $alias   Table alias for the 'module' column
     * @param array  $modules Allowed module slugs (empty = no filter)
     * @return string
     */
    private function moduleFilter(string $alias, array $modules): string
    {
        if (empty($modules)) {
            return '';
        }
        $escaped = array_map(function ($m) {
            return "'" . trim($m) . "'";
        }, $modules);
        $list = implode(',', $escaped);
        return " AND {$alias}.module IN ({$list})";
    }
}
