<?php

namespace Isjm\Modules\Sudamaseva;

/**
 * Business logic and utility functions for the Sudamaseva subscription donation system.
 *
 * Handles: formatting, status labels, receipt generation, 80G eligibility, dashboard aggregation.
 */
class SudamasevaService
{
    private SudamasevaRepository $repo;

    public function __construct(?SudamasevaRepository $repo = null)
    {
        $this->repo = $repo ?? new SudamasevaRepository();
    }

    // ============================================================
    // FORMATTING
    // ============================================================

    /**
     * Format amount in Indian currency format (e.g., ₹1,00,008).
     *
     * Same logic as DonationService::formatAmount() to maintain UI consistency.
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
     * Format a receipt number for display.
     */
    public function formatReceiptNo(?string $receiptNo): string
    {
        return $receiptNo ?? '—';
    }

    /**
     * Format a subscription status into a human-readable label with CSS class.
     */
    public function getSubscriptionStatusInfo(string $status): array
    {
        $map = [
            'active'    => ['label' => 'Active',     'class' => 'badge-success',  'icon' => 'fa-check-circle'],
            'completed' => ['label' => 'Completed',  'class' => 'badge-info',     'icon' => 'fa-check-double'],
            'paused'    => ['label' => 'Paused',     'class' => 'badge-warning',  'icon' => 'fa-pause-circle'],
            'cancelled' => ['label' => 'Cancelled',  'class' => 'badge-danger',   'icon' => 'fa-times-circle'],
        ];

        return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-secondary', 'icon' => 'fa-circle'];
    }

    /**
     * Format a payment status into a human-readable label with CSS class.
     */
    public function getPaymentStatusInfo(string $status): array
    {
        $map = [
            'paid'      => ['label' => 'Paid',       'class' => 'badge-success',  'icon' => 'fa-check-circle'],
            'created'   => ['label' => 'Created',    'class' => 'badge-info',     'icon' => 'fa-clock'],
            'attempted' => ['label' => 'Attempted',  'class' => 'badge-warning',  'icon' => 'fa-exclamation-circle'],
            'failed'    => ['label' => 'Failed',     'class' => 'badge-danger',   'icon' => 'fa-times-circle'],
        ];

        return $map[$status] ?? ['label' => ucfirst($status), 'class' => 'badge-secondary', 'icon' => 'fa-circle'];
    }

    /**
     * Format a date for display.
     */
    public function formatDate(?string $date, string $format = 'd M Y'): string
    {
        if (empty($date)) {
            return '—';
        }

        $ts = strtotime($date);
        if ($ts === false) {
            return $date;
        }

        return date($format, $ts);
    }

    /**
     * Format a date with time.
     */
    public function formatDateTime(?string $date): string
    {
        return $this->formatDate($date, 'd M Y, h:i A');
    }

    /**
     * Format a phone number for display (mask middle digits).
     */
    public function formatPhone(?string $phone, bool $mask = false): string
    {
        if (empty($phone)) {
            return '—';
        }

        if ($mask && strlen($phone) >= 10) {
            return substr($phone, 0, 4) . '****' . substr($phone, -2);
        }

        return $phone;
    }

    // ============================================================
    // BUSINESS LOGIC
    // ============================================================

    /**
     * Generate a receipt number in the format: SMS/YYYY/NNNNN.
     *
     * @param int|null $sequence Sequence number (auto-fetched if null)
     */
    public function generateReceiptNo(?int $sequence = null): string
    {
        $year = date('Y');
        $seq = $sequence ?? $this->repo->getNextReceiptSequence($year);
        return sprintf('SMS/%s/%05d', $year, $seq);
    }

    /**
     * Determine if a payment is 80G eligible based on amount and donor PAN.
     *
     * Rules (Indian Income Tax 80G):
     * - Minimum ₹200 per payment for tax exemption
     * - PAN is required for receipts ≥ ₹50,000 in a financial year
     * - PAN is recommended for all receipts
     *
     * @param int   $amount  Payment amount in INR
     * @param array $donor   Donor record (must have 'pan' key)
     * @return bool
     */
    public function isEligibleFor80G(int $amount, array $donor): bool
    {
        // No 80G deduction below ₹200 as per tax guidelines
        if ($amount < 200) {
            return false;
        }

        // PAN is required for 80G exemption claim
        if (empty($donor['pan'])) {
            return false;
        }

        return true;
    }

    /**
     * Get a human-readable label for a donor source.
     */
    public function getDonorSourceLabel(string $source): string
    {
        $map = [
            'migrated'   => 'Legacy (Old System)',
            'sudamaseva' => 'Sudamaseva (New)',
            'manual'     => 'Manual Entry',
            'api'        => 'API Import',
        ];

        return $map[$source] ?? ucfirst($source);
    }

    /**
     * Get the subscription status badge HTML.
     */
    public function renderStatusBadge(string $status, string $type = 'subscription'): string
    {
        if ($type === 'payment') {
            $info = $this->getPaymentStatusInfo($status);
        } else {
            $info = $this->getSubscriptionStatusInfo($status);
        }

        $class = htmlspecialchars($info['class']);
        $label = htmlspecialchars($info['label']);
        $icon  = htmlspecialchars($info['icon']);

        return "<span class=\"{$class}\"><i class=\"fas {$icon}\"></i> {$label}</span>";
    }

    /**
     * Get default seva amount suggestions for new subscriptions.
     */
    public function getDefaultAmounts(): array
    {
        return [
            51   => 'Tulsi Leaf (₹51)',
            101  => 'Lotus Petal (₹101)',
            251  => 'Fruit Offering (₹251)',
            501  => 'Flower Garland (₹501)',
            1001 => 'Prasadam Seva (₹1,001)',
            2501 => 'Annadaan Seva (₹2,501)',
            5001 => 'Mahaseva (₹5,001)',
        ];
    }

    // ============================================================
    // CALCULATIONS
    // ============================================================

    /**
     * Calculate the total amount a donor has paid across all subscriptions.
     */
    public function calculateDonorTotalPaid(int $donorId): int
    {
        $payments = $this->repo->getPaymentsByDonor($donorId);
        $total = 0;
        foreach ($payments as $payment) {
            if (($payment['payment_status'] ?? '') === 'paid') {
                $total += (int) ($payment['amount'] ?? 0);
            }
        }
        return $total;
    }

    /**
     * Calculate the total amount a donor has paid in a given financial year.
     *
     * Indian financial year: April 1 to March 31.
     */
    public function calculateDonorTotalPaidForFY(int $donorId, ?int $fyYear = null): int
    {
        $fyYear = $fyYear ?? (int) date('Y');
        // If current month is Jan-Mar, the financial year started last year
        $fyStartMonth = 4; // April
        $currentMonth = (int) date('n');
        if ($currentMonth < $fyStartMonth) {
            $fyYear--; // e.g., Jan 2026 → FY 2025-2026
        }

        $startDate = "{$fyYear}-04-01 00:00:00";
        $endDate = ($fyYear + 1) . "-03-31 23:59:59";

        // Use the payments-by-date approach then filter by donor via repo's date range
        // (keeps querying through the repository abstraction layer)
        $payments = $this->repo->getPaymentsByDateRange($startDate, $endDate);
        $total = 0;
        foreach ($payments as $payment) {
            if ((int) ($payment['donor_id'] ?? 0) === $donorId) {
                $total += (int) ($payment['amount'] ?? 0);
            }
        }
        return $total;
    }

    /**
     * Calculate subscription progress percentage.
     */
    public function calculateSubscriptionProgress(array $subscription): float
    {
        $total = (int) ($subscription['total_installments'] ?? 0);
        $paid  = (int) ($subscription['installments_paid'] ?? 0);

        if ($total <= 0) {
            return 0; // Open-ended subscription
        }

        return round(($paid / $total) * 100, 1);
    }

    /**
     * Get the next installment number for a subscription.
     */
    public function getNextInstallmentNumber(int $subscriptionId): int
    {
        $payments = $this->repo->getPaymentsBySubscription($subscriptionId);
        $max = 0;
        foreach ($payments as $payment) {
            $num = (int) ($payment['installment_number'] ?? 0);
            if ($num > $max) {
                $max = $num;
            }
        }
        return $max + 1;
    }

    /**
     * Calculate the remaining amount for a subscription.
     */
    public function calculateSubscriptionRemaining(array $subscription): int
    {
        $totalInstallments = (int) ($subscription['total_installments'] ?? 0);
        $installmentsPaid  = (int) ($subscription['installments_paid'] ?? 0);

        if ($totalInstallments <= 0) {
            return 0; // Open-ended — no fixed remaining
        }

        $remaining = $totalInstallments - $installmentsPaid;
        return max(0, $remaining) * (int) ($subscription['amount'] ?? 0);
    }

    /**
     * Get the dashboard summary for a donor (calls repo, then enriches with calculated data).
     */
    public function getDonorDashboard(int $donorId): array
    {
        $dashboard = $this->repo->getDonorDashboard($donorId);

        if (empty($dashboard)) {
            return [];
        }

        // Enrich with calculated data
        $dashboard['total_paid_formatted'] = $this->formatAmount($dashboard['total_paid'] ?? 0);
        $dashboard['current_fy_total'] = $this->calculateDonorTotalPaidForFY($donorId);

        if ($dashboard['active_subscription']) {
            $dashboard['active_subscription']['progress'] = $this->calculateSubscriptionProgress($dashboard['active_subscription']);
            $dashboard['active_subscription']['remaining_formatted'] = $this->formatAmount(
                $this->calculateSubscriptionRemaining($dashboard['active_subscription'])
            );
            $dashboard['active_subscription']['next_installment'] = $this->getNextInstallmentNumber((int) $dashboard['active_subscription']['id']);
            $dashboard['active_subscription']['status_badge'] = $this->renderStatusBadge($dashboard['active_subscription']['status']);
        }

        // Format dates and amounts in recent payments
        foreach ($dashboard['recent_payments'] as &$payment) {
            $payment['amount_formatted'] = $this->formatAmount((float) ($payment['amount'] ?? 0));
            $payment['date_formatted'] = $this->formatDate($payment['payment_date'] ?? null);
            $payment['status_badge'] = $this->renderStatusBadge($payment['payment_status'] ?? 'created', 'payment');
        }
        unset($payment);

        return $dashboard;
    }

    /**
     * Get a formatted label for a financial year.
     */
    public function getFinancialYearLabel(?int $fyYear = null): string
    {
        $fyYear = $fyYear ?? $this->getCurrentFinancialYear();
        return "FY {$fyYear}-" . ($fyYear + 1);
    }

    /**
     * Get the current financial year start year.
     */
    public function getCurrentFinancialYear(): int
    {
        $currentMonth = (int) date('n');
        $currentYear  = (int) date('Y');
        return ($currentMonth < 4) ? $currentYear - 1 : $currentYear;
    }

    /**
     * Get the start and end dates for a financial year.
     */
    public function getFinancialYearRange(?int $fyYear = null): array
    {
        $fyYear = $fyYear ?? $this->getCurrentFinancialYear();
        return [
            'start' => "{$fyYear}-04-01 00:00:00",
            'end'   => ($fyYear + 1) . "-03-31 23:59:59",
            'label' => $this->getFinancialYearLabel($fyYear),
        ];
    }

    // ============================================================
    // DELEGATE TO REPOSITORY (convenience methods)
    // ============================================================

    public function getDonorById(int $id): ?array
    {
        return $this->repo->getDonorById($id);
    }

    public function getDonorByUuid(string $uuid): ?array
    {
        return $this->repo->getDonorByUuid($uuid);
    }

    public function getDonorByPhone(string $phone): ?array
    {
        return $this->repo->getDonorByPhone($phone);
    }

    public function getDonors(?string $status = null, ?string $search = null, int $page = 1, int $perPage = 50, bool $hideOrphans = true): array
    {
        return $this->repo->getDonors($status, $search, $page, $perPage, $hideOrphans);
    }

    public function createDonor(array $data): int|false
    {
        return $this->repo->createDonor($data);
    }

    public function updateDonor(int $id, array $data): bool
    {
        return $this->repo->updateDonor($id, $data);
    }

    public function getDonorStats(): array
    {
        return $this->repo->getDonorStats();
    }

    public function getSubscriptionById(int $id): ?array
    {
        return $this->repo->getSubscriptionById($id);
    }

    public function getSubscriptionByRazorpayId(string $razorpayId): ?array
    {
        return $this->repo->getSubscriptionByRazorpayId($razorpayId);
    }

    public function getSubscriptionsByDonor(int $donorId): array
    {
        return $this->repo->getSubscriptionsByDonor($donorId);
    }

    public function getActiveSubscriptions(): array
    {
        return $this->repo->getActiveSubscriptions();
    }

    public function getSubscriptions(?string $status = null, ?int $donorId = null, int $page = 1, int $perPage = 50): array
    {
        return $this->repo->getSubscriptions($status, $donorId, $page, $perPage);
    }

    public function createSubscription(array $data): int|false
    {
        return $this->repo->createSubscription($data);
    }

    public function updateSubscription(int $id, array $data): bool
    {
        return $this->repo->updateSubscription($id, $data);
    }

    public function completeSubscription(int $id): bool
    {
        return $this->repo->completeSubscription($id);
    }

    public function cancelSubscription(int $id): bool
    {
        return $this->repo->cancelSubscription($id);
    }

    public function incrementInstallmentsPaid(int $subscriptionId): bool
    {
        return $this->repo->incrementInstallmentsPaid($subscriptionId);
    }

    public function getSubscriptionStats(): array
    {
        return $this->repo->getSubscriptionStats();
    }

    public function getPaymentById(int $id): ?array
    {
        return $this->repo->getPaymentById($id);
    }

    public function getPaymentByRazorpayId(string $razorpayPaymentId): ?array
    {
        return $this->repo->getPaymentByRazorpayId($razorpayPaymentId);
    }

    public function getPaymentsBySubscription(int $subscriptionId): array
    {
        return $this->repo->getPaymentsBySubscription($subscriptionId);
    }

    public function getPaymentsByDonor(int $donorId, ?int $limit = null): array
    {
        return $this->repo->getPaymentsByDonor($donorId, $limit);
    }

    public function getPaymentsByDateRange(string $from, string $to): array
    {
        return $this->repo->getPaymentsByDateRange($from, $to);
    }

    public function getPaymentsWithoutReceipts(): array
    {
        return $this->repo->getPaymentsWithoutReceipts();
    }

    public function createPayment(array $data): int|false
    {
        return $this->repo->createPayment($data);
    }

    public function updatePayment(int $id, array $data): bool
    {
        return $this->repo->updatePayment($id, $data);
    }

    public function getPaymentStats(?string $from = null, ?string $to = null): array
    {
        return $this->repo->getPaymentStats($from, $to);
    }

    public function getNextReceiptSequence(string $year = null): int
    {
        return $this->repo->getNextReceiptSequence($year);
    }

    public function getReceiptById(int $id): ?array
    {
        return $this->repo->getReceiptById($id);
    }

    public function getReceiptByNumber(string $receiptNo): ?array
    {
        return $this->repo->getReceiptByNumber($receiptNo);
    }

    public function getReceiptsByPayment(int $paymentId): array
    {
        return $this->repo->getReceiptsByPayment($paymentId);
    }

    public function getReceiptsByDonor(int $donorId): array
    {
        return $this->repo->getReceiptsByDonor($donorId);
    }

    public function getReceipts(?string $from = null, ?string $to = null, int $page = 1, int $perPage = 50): array
    {
        return $this->repo->getReceipts($from, $to, $page, $perPage);
    }

    public function createReceipt(array $data): int|false
    {
        return $this->repo->createReceipt($data);
    }

    public function getReceiptStats(): array
    {
        return $this->repo->getReceiptStats();
    }

    public function getRecentPayments(int $limit = 20): array
    {
        return $this->repo->getRecentPayments($limit);
    }

    public function getMonthlyRevenue(int $months = 12): array
    {
        return $this->repo->getMonthlyRevenue($months);
    }

    public function getDashboardStats(): array
    {
        return $this->repo->getDashboardStats();
    }

    public function getPaymentsForExport(string $from, string $to): array
    {
        return $this->repo->getPaymentsForExport($from, $to);
    }
}
