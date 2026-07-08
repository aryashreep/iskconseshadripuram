<?php

namespace Isjm\Modules\Donation;

/**
 * Business logic and utility functions for the donation system.
 * 
 * Handles: formatting, category info, homepage queries, seasonal spotlight.
 */
class DonationService
{
    private DonationRepository $repo;

    public function __construct(?DonationRepository $repo = null)
    {
        $this->repo = $repo ?? new DonationRepository();
    }

    // ============================================================
    // FORMATTING
    // ============================================================

    /**
     * Format amount in Indian currency format (e.g., ₹1,00,008)
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
     * Get category display name and icon for UI section labels.
     */
    public function getCategoryInfo(string $category): array
    {
        $info = [
            'festival'      => ['label' => 'Grand Festivals',        'icon' => 'fa-star'],
            'ekadashi'      => ['label' => 'Ekadashi',               'icon' => 'fa-moon'],
            'appearance'    => ['label' => 'Appearance Days',         'icon' => 'fa-sun'],
            'disappearance' => ['label' => 'Disappearance Days',      'icon' => 'fa-circle'],
            'event'         => ['label' => 'Events & Programs',       'icon' => 'fa-calendar'],
            'service'       => ['label' => 'Seva & Services',         'icon' => 'fa-hand-holding-heart'],
            'construction'  => ['label' => 'Temple Construction',     'icon' => 'fa-hard-hat'],
            'general'       => ['label' => 'General Donations',       'icon' => 'fa-heart'],
        ];

        return $info[$category] ?? ['label' => ucfirst($category), 'icon' => 'fa-circle'];
    }

    /**
     * Parse quick_stats string into an associative array.
     * Format: "Key: Value|Key2: Value2"
     */
    public function parseQuickStats(?string $stats): array
    {
        if (empty($stats)) return [];
        $result = [];
        $pairs = explode('|', $stats);
        foreach ($pairs as $pair) {
            $parts = explode(':', $pair, 2);
            if (count($parts) === 2) {
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $result;
    }

    // ============================================================
    // CAUSE LOGIC
    // ============================================================

    /**
     * Group causes by category for display.
     */
    public function getCausesGrouped(): array
    {
        $causes = $this->repo->getCauses();
        $grouped = [];
        foreach ($causes as $cause) {
            $cat = $cause['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $cause;
        }
        return $grouped;
    }

    /**
     * Get a cause by slug (convenience wrapper).
     */
    public function getCauseBySlug(string $slug): ?array
    {
        return $this->repo->getCauseBySlug($slug);
    }

    // ============================================================
    // HOMEPAGE HELPERS
    // ============================================================

    /**
     * Get seasonal spotlight cause based on current month.
     */
    public function getSeasonalCause(?int $month = null): ?array
    {
        $month = $month ?: (int) date('n');

        $map = [
            1  => 'nityananda-trayodashi',
            2  => 'gaura-purnima',
            3  => 'rama-navami',
            4  => 'narasimha-chaturdashi',
            5  => 'snana-yatra',
            6  => 'ratha-yatra',
            7  => 'jhulan-yatra',
            8  => 'janmashtami',
            9  => 'radhashtami',
            10 => 'diwali',
            11 => 'govardhan-puja',
            12 => 'gita-jayanti',
        ];

        $slug = $map[$month] ?? 'janmashtami';
        return $this->repo->getCauseBySlug($slug);
    }

    /**
     * Get display month name for seasonal spotlight.
     */
    public function getSeasonalMonthLabel(?int $month = null): string
    {
        $month = $month ?: (int) date('n');
        $names = [
            1  => 'January',   2  => 'February',  3  => 'March',
            4  => 'April',     5  => 'May',       6  => 'June',
            7  => 'July',      8  => 'August',    9  => 'September',
            10 => 'October',   11 => 'November',  12 => 'December',
        ];
        return $names[$month] ?? '';
    }

    /**
     * Get category tile data for the "Explore" section.
     */
    public function getCategoryTiles(): array
    {
        return [
            [
                'title' => 'Temple Services',
                'desc' => 'Daily seva, prasadam, and worship',
                'icon' => 'fa-hand-holding-heart',
                'link' => 'services',
                'color' => 'var(--primary)',
            ],
            [
                'title' => 'Grand Festivals',
                'desc' => 'Celebrate with Lord Jagannath',
                'icon' => 'fa-star',
                'link' => 'festivals/grand-festivals',
                'color' => 'var(--accent)',
            ],
            [
                'title' => 'Appearance Days',
                'desc' => 'Honor our beloved acharyas',
                'icon' => 'fa-sun',
                'link' => 'festivals/appearance',
                'color' => '#e8944a',
            ],
            [
                'title' => 'Disappearance Days',
                'desc' => 'Remember their sacred legacy',
                'icon' => 'fa-circle',
                'link' => 'festivals/disappearance',
                'color' => '#7b1e1e',
            ],
            [
                'title' => 'Ekadashi',
                'desc' => 'Sacred day of fasting & prayer',
                'icon' => 'fa-moon',
                'link' => 'festivals/ekadashi',
                'color' => '#4a6fa5',
            ],
            [
                'title' => 'Special Events',
                'desc' => 'Caturmasya, ceremonies & more',
                'icon' => 'fa-calendar',
                'link' => 'festivals/events',
                'color' => '#2e7d32',
            ],
        ];
    }

    // ============================================================
    // DELEGATE TO REPOSITORY (convenience methods)
    // ============================================================

    public function getHomepageServiceCauses(int $limit = 6): array
    {
        return $this->repo->getHomepageServiceCauses($limit);
    }

    public function getHomepageFestivalCauses(int $limit = 6): array
    {
        return $this->repo->getHomepageFestivalCauses($limit);
    }

    public function getFestivalDetail(string $slug): ?array
    {
        return $this->repo->getFestivalDetail($slug);
    }

    public function getFestivalsByCategory(string $category, bool $timeBoundOnly = false): array
    {
        return $this->repo->getFestivalsByCategory($category, $timeBoundOnly);
    }

    public function getCauseSevasGrouped(int $causeId): array
    {
        return $this->repo->getCauseSevasGrouped($causeId);
    }

    public function getCauseSevas(int $causeId): array
    {
        return $this->repo->getCauseSevas($causeId);
    }

    public function getDefaultCauseAmount(array $cause): float
    {
        return $this->repo->getDefaultCauseAmount($cause);
    }

    public function getRelatedCauses(array $cause, int $limit = 4): array
    {
        return $this->repo->getRelatedCauses($cause, $limit);
    }
}
