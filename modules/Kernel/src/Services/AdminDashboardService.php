<?php

namespace Isjm\Services;

use Isjm\Repositories\AdminDashboardRepository;

/**
 * Business logic and formatting for the consolidated admin dashboard.
 *
 * Aggregates revenue from all modules:
 *   Donations, Puja, Yagya, Panihati, Sudamaseva
 */
class AdminDashboardService
{
    private AdminDashboardRepository $repo;

    /** Map of module slug → display label */
    private array $moduleLabels = [
        'donations'  => 'General Donations',
        'puja'       => 'Puja',
        'yagya'      => 'Yagya',
        'panihati'   => 'Panihati Yatra',
        'sudamaseva' => 'Sudamaseva',
    ];

    /** Map of module slug → icon class */
    private array $moduleIcons = [
        'donations'  => 'fa-hand-holding-heart',
        'puja'       => 'fa-scroll',
        'yagya'      => 'fa-fire',
        'panihati'   => 'fa-bus',
        'sudamaseva' => 'fa-seedling',
    ];

    /** Map of module slug → colour for charts */
    private array $moduleColors = [
        'donations'  => '#c86b1f',
        'puja'       => '#0b5ed7',
        'yagya'      => '#c62828',
        'panihati'   => '#2e7d32',
        'sudamaseva' => '#d4af37',
    ];

    public function __construct(?AdminDashboardRepository $repo = null)
    {
        $this->repo = $repo ?? new AdminDashboardRepository();
    }

    // ============================================================
    // PUBLIC API
    // ============================================================

    /**
     * Get all overview data in one call.
     *
     * @param array       $visibleModules
     * @param string|null $from  Start date filter (Y-m-d)
     * @param string|null $to    End date filter (Y-m-d)
     */
    public function getOverview(array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        return $this->repo->getOverview($visibleModules, $from, $to);
    }

    /**
     * Get module breakdown with labels/icons/colours pre-applied.
     *
     * @param array       $visibleModules
     * @param string|null $from  Start date filter (Y-m-d)
     * @param string|null $to    End date filter (Y-m-d)
     */
    public function getModuleBreakdown(array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        $rows = $this->repo->getModuleBreakdown($visibleModules, $from, $to);
        $totalAmount = array_sum(array_column($rows, 'total_amount'));

        $result = [];
        foreach ($rows as $row) {
            $module = $row['module'];
            $amount = (float) ($row['total_amount'] ?? 0);
            $result[] = [
                'module'           => $module,
                'label'            => $this->moduleLabels[$module] ?? ucfirst($module),
                'icon'             => $this->moduleIcons[$module] ?? 'fa-circle',
                'color'            => $this->moduleColors[$module] ?? '#757575',
                'total_amount'     => $amount,
                'total_formatted'  => $this->formatAmount($amount),
                'payment_count'    => (int) ($row['payment_count'] ?? 0),
                'this_month'       => (float) ($row['this_month_amount'] ?? 0),
                'this_month_formatted' => $this->formatAmount((float) ($row['this_month_amount'] ?? 0)),
                'share_pct'        => $totalAmount > 0 ? round(($amount / $totalAmount) * 100, 1) : 0,
            ];
        }

        return $result;
    }

    /**
     * Get monthly trend data formatted for Chart.js stacked chart.
     *
     * @param int         $months
     * @param array       $visibleModules
     * @param string|null $from  Start date filter (Y-m-d)
     * @param string|null $to    End date filter (Y-m-d)
     * @return array Keys: labels (month labels), datasets (per module), modules (list)
     */
    public function getMonthlyTrendChart(int $months = 12, array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        $rows = $this->repo->getMonthlyTrend($months, $visibleModules, $from, $to);

        // Collect all month labels
        $monthsSet = [];
        $moduleData = [];

        foreach ($rows as $row) {
            $label = $row['month_label'];
            $module = $row['module'];
            $amount = (float) ($row['total_amount'] ?? 0);

            $monthsSet[$row['yr_month']] = $label;

            if (!isset($moduleData[$module])) {
                $moduleData[$module] = [];
            }
            $moduleData[$module][$row['yr_month']] = $amount;
        }

        ksort($monthsSet);
        $labels = array_values($monthsSet);
        $yearMonths = array_keys($monthsSet);

        // Build datasets
        $usedModules = array_keys($moduleData);
        $moduleOrder = ['donations', 'puja', 'yagya', 'panihati', 'sudamaseva'];
        $datasets = [];

        foreach ($moduleOrder as $mod) {
            if (!in_array($mod, $usedModules)) {
                continue;
            }
            $data = [];
            foreach ($yearMonths as $ym) {
                $data[] = $moduleData[$mod][$ym] ?? 0;
            }
            $datasets[] = [
                'label'           => $this->moduleLabels[$mod] ?? ucfirst($mod),
                'data'            => $data,
                'backgroundColor' => $this->moduleColors[$mod] ?? '#757575',
                'color'           => $this->moduleColors[$mod] ?? '#757575',
            ];
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
            'modules'  => $usedModules,
        ];
    }

    /**
     * Get recent collections.
     *
     * @param int         $limit
     * @param array       $visibleModules
     * @param string|null $from  Start date filter (Y-m-d)
     * @param string|null $to    End date filter (Y-m-d)
     */
    public function getRecentCollections(int $limit = 10, array $visibleModules = [], ?string $from = null, ?string $to = null): array
    {
        $rows = $this->repo->getRecentCollections($limit, $visibleModules, $from, $to);

        foreach ($rows as &$row) {
            $row['module_label'] = $this->moduleLabels[$row['module']] ?? ucfirst($row['module']);
            $row['module_icon']  = $this->moduleIcons[$row['module']] ?? 'fa-circle';
            $row['module_color'] = $this->moduleColors[$row['module']] ?? '#757575';
            $row['amount_formatted'] = $this->formatAmount((float) ($row['amount'] ?? 0));
            $row['date_formatted'] = $this->formatDate($row['paid_at'] ?? null);
            $row['link'] = $this->getModuleLink($row['module'], $row['source_id']);
        }
        unset($row);

        return $rows;
    }

    /**
     * Get recurring pipeline.
     */
    public function getRecurringPipeline(): array
    {
        $pipeline = $this->repo->getRecurringPipeline();
        $pipeline['donation_subs_monthly_formatted'] = $this->formatAmount($pipeline['donation_subs_monthly']);
        $pipeline['sudamaseva_subs_monthly_formatted'] = $this->formatAmount($pipeline['sudamaseva_subs_monthly']);
        $pipeline['total_monthly_formatted'] = $this->formatAmount($pipeline['total_monthly']);
        return $pipeline;
    }

    /**
     * Get donation category split (for existing chart).
     */
    public function getDonationCategorySplit(): array
    {
        return $this->repo->getDonationCategorySplit();
    }

    /**
     * Get module metadata (labels, icons, colors).
     */
    public function getModuleMeta(string $module): array
    {
        return [
            'label' => $this->moduleLabels[$module] ?? ucfirst($module),
            'icon'  => $this->moduleIcons[$module] ?? 'fa-circle',
            'color' => $this->moduleColors[$module] ?? '#757575',
        ];
    }

    public function getAllModuleMeta(): array
    {
        $meta = [];
        foreach (array_keys($this->moduleLabels) as $mod) {
            $meta[$mod] = $this->getModuleMeta($mod);
        }
        return $meta;
    }
    // ============================================================
    // FORMATTING HELPERS
    // ============================================================

    /**
     * Format amount in Indian currency (₹1,00,000).
     */
    public function formatAmount(float $amount): string
    {
        $negative = $amount < 0;
        $amount = abs($amount);
        $whole = floor($amount);
        $decimal = round(($amount - $whole) * 100);
        $wholeStr = (string) $whole;
        $len = strlen($wholeStr);

        if ($len <= 3) {
            $formatted = $wholeStr;
        } else {
            $last3 = substr($wholeStr, -3);
            $rest = substr($wholeStr, 0, $len - 3);
            $groups = [];
            while (strlen($rest) > 0) {
                if (strlen($rest) >= 2) {
                    array_unshift($groups, substr($rest, -2));
                    $rest = substr($rest, 0, strlen($rest) - 2);
                } else {
                    array_unshift($groups, $rest);
                    break;
                }
            }
            $formatted = implode(',', $groups) . ',' . $last3;
        }

        $result = html_entity_decode('&#8377;', ENT_QUOTES, 'UTF-8') . $formatted;
        if ($decimal > 0) {
            $result .= '.' . str_pad((string) $decimal, 2, '0', STR_PAD_LEFT);
        }
        return $negative ? '-' . $result : $result;
    }

    /**
     * Format a date for display.
     */
    public function formatDate(?string $date, string $format = 'd M Y, h:i A'): string
    {
        if (empty($date)) {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : $date;
    }

    /**
     * Build a deep-link to the source record in the admin panel.
     */
    public function getModuleLink(string $module, int $sourceId): ?string
    {
        $map = [
            'donations'  => 'admin/donations?id=',
            'puja'       => 'admin/bookings?id=',
            'yagya'      => 'admin/bookings?id=',
            'panihati'   => 'admin/panihati-yatra?id=',
            'sudamaseva' => 'admin/sudamaseva-donor-detail?id=',
        ];

        $base = $map[$module] ?? null;
        return $base ? $base . $sourceId : null;
    }
}
