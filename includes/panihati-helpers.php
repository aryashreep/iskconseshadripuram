<?php
/**
 * Panihati Yatra Helpers
 *
 * Provides reusable functions for dynamic pricing and related utilities.
 */

/**
 * Get Panihati Yatra pricing for a given year.
 *
 * Returns an associative array with:
 *   - bus_adult_price
 *   - bus_kid_price
 *   - vehicle_adult_price
 *   - vehicle_kid_price
 *
 * Falls back to the current year if no pricing exists for the given year.
 * Falls back to hardcoded defaults if no pricing exists at all.
 *
 * @param int|null $year The year to look up (defaults to current year)
 * @return array
 */
function getPanihatiPricing(?int $year = null): array
{
    static $cache = [];

    if ($year === null) {
        $year = (int)date('Y');
    }

    if (isset($cache[$year])) {
        return $cache[$year];
    }

    // Default pricing as fallback
    $defaults = [
        'bus_adult_price' => 1000,
        'bus_kid_price' => 600,
        'vehicle_adult_price' => 600,
        'vehicle_kid_price' => 600,
    ];

    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM `panihati_pricing` WHERE `year` = ? LIMIT 1");
        $stmt->execute([$year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $pricing = [
                'bus_adult_price' => (float)$row['bus_adult_price'],
                'bus_kid_price' => (float)$row['bus_kid_price'],
                'vehicle_adult_price' => (float)$row['vehicle_adult_price'],
                'vehicle_kid_price' => (float)$row['vehicle_kid_price'],
            ];
        } else {
            $pricing = $defaults;
        }
    } catch (Exception $e) {
        $pricing = $defaults;
    }

    $cache[$year] = $pricing;
    return $pricing;
}

/**
 * Calculate the expected amount (in rupees) for a Panihati registration.
 *
 * @param string $travelMode 'bus' or 'own_vehicle'
 * @param int $adultsCount Number of adults
 * @param int $kidsCount Number of kids
 * @param int|null $year Registration year (defaults to current year)
 * @return float Expected amount in rupees
 */
function calculatePanihatiAmount(string $travelMode, int $adultsCount, int $kidsCount, ?int $year = null): float
{
    $pricing = getPanihatiPricing($year);

    if ($travelMode === 'bus') {
        return ($adultsCount * $pricing['bus_adult_price']) + ($kidsCount * $pricing['bus_kid_price']);
    } else {
        return ($adultsCount * $pricing['vehicle_adult_price']) + ($kidsCount * $pricing['vehicle_kid_price']);
    }
}

/**
 * Format a pricing array into a human-readable string for a specific travel mode.
 *
 * @param array $pricing Pricing array from getPanihatiPricing()
 * @param string $mode 'bus' or 'own_vehicle'
 * @return string e.g. "₹1000 / adult, ₹600 / kid"
 */
function formatPanihatiPricing(array $pricing, string $mode): string
{
    if ($mode === 'bus') {
        return '₹' . number_format($pricing['bus_adult_price']) . ' / adult, ₹' . number_format($pricing['bus_kid_price']) . ' / kid';
    }
    return '₹' . number_format($pricing['vehicle_adult_price']) . ' / adult, ₹' . number_format($pricing['vehicle_kid_price']) . ' / kid';
}

/**
 * Get pricing formatted as a simple rate label (e.g., "₹1000 / adult").
 */
function getPanihatiRateLabel(array $pricing, string $mode): string
{
    if ($mode === 'bus') {
        return '₹' . number_format($pricing['bus_adult_price']) . ' / adult';
    }
    return '₹' . number_format($pricing['vehicle_adult_price']) . ' / person';
}
