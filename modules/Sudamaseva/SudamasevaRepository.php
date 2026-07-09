<?php

namespace Isjm\Modules\Sudamaseva;

use PDO;
use PDOException;

/**
 * Repository for all Sudamaseva subscription donation database operations.
 *
 * Handles: donors, subscriptions, payments, receipts.
 * Follows the same pattern as DonationRepository.
 */
class SudamasevaRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDB();
    }

    // ============================================================
    // DONOR QUERIES
    // ============================================================

    /**
     * Get a donor by their internal ID.
     */
    public function getDonorById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sudamaseva_donors WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonorById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a donor by their UUID.
     */
    public function getDonorByUuid(string $uuid): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sudamaseva_donors WHERE uuid = ? LIMIT 1");
            $stmt->execute([$uuid]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonorByUuid error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a donor by their phone number (UNIQUE constraint).
     */
    public function getDonorByPhone(string $phone): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sudamaseva_donors WHERE phone = ? LIMIT 1");
            $stmt->execute([$phone]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonorByPhone error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a donor by phone number OR legacy ID.
     * Used for public donor lookup where the user can enter either.
     */
    public function findDonorByPhoneOrLegacyId(string $query): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM sudamaseva_donors
                WHERE phone = ? OR legacy_id_no = ?
                LIMIT 1
            ");
            $stmt->execute([$query, $query]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::findDonorByPhoneOrLegacyId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search/List donors with optional filters.
     *
     * @param string|null $status  Filter by status: 'active', 'inactive', 'paused', or null for all
     * @param string|null $search  Search term matching donor_name, phone, or email
     * @param int         $page    Page number (1-indexed)
     * @param int         $perPage Results per page
     * @return array Returns ['donors' => [...], 'total' => int, 'pages' => int]
     */
    public function getDonors(?string $status = null, ?string $search = null, int $page = 1, int $perPage = 50, bool $hideOrphans = true): array
    {
        try {
            $where = [];
            $params = [];

            if ($status) {
                $where[] = "status = ?";
                $params[] = $status;
            }

            if ($search) {
                $where[] = "(donor_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
                $searchTerm = '%' . $search . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($hideOrphans) {
                $where[] = "phone NOT LIKE 'orphan-%'";
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $offset = max(0, ($page - 1) * $perPage);

            // Count total matching donors
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM sudamaseva_donors {$whereClause}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = (int) ceil($total / $perPage);

            // Fetch page
            $stmt = $this->db->prepare(
                "SELECT *, 
                        (SELECT id FROM sudamaseva_subscriptions WHERE donor_id = sudamaseva_donors.id AND status = 'active' LIMIT 1) as active_sub_id,
                        (SELECT MAX(cycle) FROM sudamaseva_subscriptions WHERE donor_id = sudamaseva_donors.id) as max_cycle
                 FROM sudamaseva_donors {$whereClause} ORDER BY created_at DESC LIMIT ? OFFSET ?"
            );
            $paramIndex = 1;
            foreach ($params as $pVal) {
                $stmt->bindValue($paramIndex++, $pVal, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
            $stmt->execute();
            $donors = $stmt->fetchAll();

            return [
                'donors' => $donors,
                'total' => $total,
                'pages' => $pages,
                'page' => $page,
                'per_page' => $perPage,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonors error: ' . $e->getMessage());
            return ['donors' => [], 'total' => 0, 'pages' => 0, 'page' => 1, 'per_page' => $perPage];
        }
    }

    /**
     * Create a new donor record.
     *
     * @param array $data Keys: donor_name, phone, [uuid], [email], [pan], [area], [city], [state], [source], [notes], [status]
     * @return int|false The new donor ID, or false on failure
     */
    public function createDonor(array $data): int|false
    {
        try {
            $uuid = $data['uuid'] ?? $this->generateUuid();

            $stmt = $this->db->prepare("
                INSERT INTO sudamaseva_donors
                (uuid, donor_name, phone, email, pan, area, city, state, source, notes, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $uuid,
                $data['donor_name'],
                $data['phone'] ?? '',
                $data['email'] ?? null,
                $data['pan'] ?? null,
                $data['area'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
                $data['source'] ?? 'sudamaseva',
                $data['notes'] ?? null,
                $data['status'] ?? 'active',
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::createDonor error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing donor record.
     * Only updates the columns provided in $data.
     */
    public function updateDonor(int $id, array $data): bool
    {
        try {
            $allowedColumns = [
                'donor_name', 'phone', 'email', 'pan', 'area', 'city', 'state',
                'source', 'notes', 'status',
            ];

            $sets = [];
            $params = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns, true)) {
                    continue;
                }
                $sets[] = "`{$key}` = ?";
                $params[] = $value;
            }

            $params[] = $id;
            $stmt = $this->db->prepare(
                "UPDATE sudamaseva_donors SET " . implode(', ', $sets) . " WHERE id = ?"
            );

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::updateDonor error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get donor statistics.
     */
    public function getDonorStats(): array
    {
        try {
            $total = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_donors WHERE phone NOT LIKE 'orphan-%'")->fetchColumn();
            $active = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_donors WHERE status = 'active' AND phone NOT LIKE 'orphan-%'")->fetchColumn();
            $migrated = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_donors WHERE source = 'migrated' AND phone NOT LIKE 'orphan-%'")->fetchColumn();
            $newSignups = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_donors WHERE source = 'sudamaseva' AND DATE(created_at) = CURDATE()")->fetchColumn();

            return [
                'total' => $total,
                'active' => $active,
                'migrated' => $migrated,
                'new_signups_today' => $newSignups,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonorStats error: ' . $e->getMessage());
            return ['total' => 0, 'active' => 0, 'migrated' => 0, 'new_signups_today' => 0];
        }
    }

    // ============================================================
    // SUBSCRIPTION QUERIES
    // ============================================================

    /**
     * Get a subscription by its ID.
     */
    public function getSubscriptionById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_subscriptions s
                JOIN sudamaseva_donors d ON s.donor_id = d.id
                WHERE s.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get subscription by Razorpay subscription ID.
     */
    public function getSubscriptionByRazorpayId(string $razorpayId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_subscriptions s
                JOIN sudamaseva_donors d ON s.donor_id = d.id
                WHERE s.razorpay_subscription_id = ?
                LIMIT 1
            ");
            $stmt->execute([$razorpayId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionByRazorpayId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all subscriptions for a donor.
     */
    public function getSubscriptionsByDonor(int $donorId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM sudamaseva_subscriptions 
                WHERE donor_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$donorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionsByDonor error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all currently active subscriptions.
     */
    public function getActiveSubscriptions(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT s.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_subscriptions s
                JOIN sudamaseva_donors d ON s.donor_id = d.id
                WHERE s.status = 'active'
                ORDER BY s.created_at DESC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getActiveSubscriptions error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Search/list subscriptions with optional filters.
     */
    public function getSubscriptions(?string $status = null, ?int $donorId = null, int $page = 1, int $perPage = 50): array
    {
        try {
            $where = [];
            $params = [];

            if ($status) {
                $where[] = "s.status = ?";
                $params[] = $status;
            }

            if ($donorId) {
                $where[] = "s.donor_id = ?";
                $params[] = $donorId;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $offset = max(0, ($page - 1) * $perPage);

            // Count
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM sudamaseva_subscriptions s {$whereClause}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = (int) ceil($total / $perPage);

            // Fetch
            $stmt = $this->db->prepare("
                SELECT s.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_subscriptions s
                JOIN sudamaseva_donors d ON s.donor_id = d.id
                {$whereClause}
                ORDER BY s.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $paramIndex = 1;
            foreach ($params as $pVal) {
                $stmt->bindValue($paramIndex++, $pVal, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'subscriptions' => $stmt->fetchAll(),
                'total' => $total,
                'pages' => $pages,
                'page' => $page,
                'per_page' => $perPage,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptions error: ' . $e->getMessage());
            return ['subscriptions' => [], 'total' => 0, 'pages' => 0, 'page' => 1, 'per_page' => $perPage];
        }
    }

    /**
     * Create a new subscription record.
     *
     * @param array $data Keys: donor_id, amount, [razorpay_subscription_id], [razorpay_plan_id], [status], [start_date], [total_installments], [source]
     * @return int|false New subscription ID, or false on failure
     */
    public function createSubscription(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sudamaseva_subscriptions
                (donor_id, amount, razorpay_subscription_id, razorpay_plan_id, status,
                 start_date, total_installments, installments_paid, source, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, NOW())
            ");

            $stmt->execute([
                $data['donor_id'],
                $data['amount'],
                $data['razorpay_subscription_id'] ?? null,
                $data['razorpay_plan_id'] ?? null,
                $data['status'] ?? 'active',
                $data['start_date'] ?? date('Y-m-d H:i:s'),
                $data['total_installments'] ?? 0,
                $data['source'] ?? 'new',
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::createSubscription error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update subscription status and counters.
     */
    public function updateSubscription(int $id, array $data): bool
    {
        try {
            $allowedColumns = [
                'amount', 'razorpay_subscription_id', 'razorpay_plan_id', 'status',
                'start_date', 'end_date', 'total_installments', 'installments_paid',
            ];

            $sets = [];
            $params = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns, true)) {
                    continue;
                }
                $sets[] = "`{$key}` = ?";
                $params[] = $value;
            }

            $params[] = $id;
            $stmt = $this->db->prepare(
                "UPDATE sudamaseva_subscriptions SET " . implode(', ', $sets) . " WHERE id = ?"
            );

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::updateSubscription error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convenience: mark a subscription as completed.
     */
    public function completeSubscription(int $id): bool
    {
        return $this->updateSubscription($id, [
            'status' => 'completed',
            'end_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Convenience: cancel a subscription.
     */
    public function cancelSubscription(int $id): bool
    {
        return $this->updateSubscription($id, [
            'status' => 'cancelled',
            'end_date' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Increment the installments_paid counter for a subscription.
     */
    public function incrementInstallmentsPaid(int $subscriptionId): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE sudamaseva_subscriptions 
                SET installments_paid = installments_paid + 1 
                WHERE id = ?
            ");
            return $stmt->execute([$subscriptionId]);
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::incrementInstallmentsPaid error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the maximum cycle number for a donor across all their subscriptions.
     * Used to determine the next cycle number when a donor renews.
     */
    public function getMaxCycleForDonor(int $donorId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(cycle), 0) FROM sudamaseva_subscriptions WHERE donor_id = ?");
            $stmt->execute([$donorId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getMaxCycleForDonor error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get subscription statistics.
     */
    public function getSubscriptionStats(): array
    {
        try {
            $total = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions")->fetchColumn();
            $active = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE status = 'active'")->fetchColumn();
            $completed = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE status = 'completed'")->fetchColumn();
            $cancelled = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE status = 'cancelled'")->fetchColumn();
            $migrated = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE source = 'migrated'")->fetchColumn();
            $newOnes = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_subscriptions WHERE source = 'new'")->fetchColumn();

            $totalMonthlyAmount = (int) $this->db->query("SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_subscriptions WHERE status = 'active'")->fetchColumn();

            return [
                'total' => $total,
                'active' => $active,
                'completed' => $completed,
                'cancelled' => $cancelled,
                'migrated' => $migrated,
                'new' => $newOnes,
                'total_monthly_amount' => $totalMonthlyAmount,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionStats error: ' . $e->getMessage());
            return [
                'total' => 0, 'active' => 0, 'completed' => 0, 'cancelled' => 0,
                'migrated' => 0, 'new' => 0, 'total_monthly_amount' => 0,
            ];
        }
    }

    // ============================================================
    // PAYMENT QUERIES
    // ============================================================

    /**
     * Get a payment by its ID.
     */
    public function getPaymentById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, s.amount as subscription_amount, d.donor_name, d.phone
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_subscriptions s ON p.subscription_id = s.id
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE p.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get payment by Razorpay payment ID.
     */
    public function getPaymentByRazorpayId(string $razorpayPaymentId): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.donor_name, d.phone
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE p.razorpay_payment_id = ?
                LIMIT 1
            ");
            $stmt->execute([$razorpayPaymentId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentByRazorpayId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all payments for a subscription, ordered by installment number.
     */
    public function getPaymentsBySubscription(int $subscriptionId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM sudamaseva_payments 
                WHERE subscription_id = ? 
                ORDER BY installment_number ASC, payment_date ASC
            ");
            $stmt->execute([$subscriptionId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentsBySubscription error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the set of paid installment numbers for a subscription.
     * Returns an array of integers (e.g. [1, 2, 3]).
     */
    public function getPaidInstallmentNumbers(int $subscriptionId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT installment_number FROM sudamaseva_payments
                WHERE subscription_id = ? AND payment_status = 'paid'
                ORDER BY installment_number ASC
            ");
            $stmt->execute([$subscriptionId]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaidInstallmentNumbers error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the next unpaid installment number for a subscription.
     * Returns null if all installments are paid or subscription is completed.
     */
    public function getNextUnpaidInstallment(int $subscriptionId): ?int
    {
        try {
            $paid = $this->getPaidInstallmentNumbers($subscriptionId);

            $stmt = $this->db->prepare("SELECT total_installments FROM sudamaseva_subscriptions WHERE id = ?");
            $stmt->execute([$subscriptionId]);
            $row = $stmt->fetch();

            if (!$row) {
                return null;
            }

            $total = (int) ($row['total_installments'] ?? 0);
            if ($total <= 0) {
                // Open-ended: next unpaid is last paid + 1, or 1 if none paid
                return empty($paid) ? 1 : max($paid) + 1;
            }

            for ($i = 1; $i <= $total; $i++) {
                if (!in_array($i, $paid)) {
                    return $i;
                }
            }

            return null; // All paid
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getNextUnpaidInstallment error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get subscription with all its payments, donor info, and schedule details.
     * Returns the subscription row with nested payments and computed fields.
     */
    public function getSubscriptionWithPayments(int $subscriptionId): ?array
    {
        try {
            $sub = $this->getSubscriptionById($subscriptionId);
            if (!$sub) {
                return null;
            }

            $sub['payments'] = $this->getPaymentsBySubscription($subscriptionId);
            $sub['paid_installments'] = $this->getPaidInstallmentNumbers($subscriptionId);
            $sub['next_unpaid'] = $this->getNextUnpaidInstallment($subscriptionId);

            return $sub;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionWithPayments error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all subscriptions (both recurring and manual) for a donor.
     * Includes collection_mode for distinguishing payment method.
     */
    public function getSubscriptionsByDonorWithMode(int $donorId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, 
                    (SELECT COUNT(*) FROM sudamaseva_payments p WHERE p.subscription_id = s.id AND p.payment_status = 'paid') as paid_count
                FROM sudamaseva_subscriptions s
                WHERE s.donor_id = ?
                ORDER BY s.created_at DESC
            ");
            $stmt->execute([$donorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getSubscriptionsByDonorWithMode error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent payments for a donor.
     */
    public function getPaymentsByDonor(int $donorId, ?int $limit = null): array
    {
        try {
            $sql = "
                SELECT p.*, s.amount as subscription_amount
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_subscriptions s ON p.subscription_id = s.id
                WHERE p.donor_id = ?
                ORDER BY p.payment_date DESC
            ";

            if ($limit) {
                $sql .= " LIMIT " . (int) $limit;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$donorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentsByDonor error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payments within a date range.
     */
    public function getPaymentsByDateRange(string $from, string $to): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE p.payment_status = 'paid'
                  AND p.payment_date >= ?
                  AND p.payment_date < ?
                ORDER BY p.payment_date ASC
            ");
            $stmt->execute([$from, $to]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentsByDateRange error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payments that need receipts (paid but no receipt record).
     */
    public function getPaymentsWithoutReceipts(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT p.*, d.donor_name, d.phone, d.email, d.pan
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE p.payment_status = 'paid'
                  AND p.id NOT IN (SELECT payment_id FROM sudamaseva_receipts)
                ORDER BY p.payment_date ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentsWithoutReceipts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new payment record.
     *
     * @param array $data Keys: [subscription_id], [donor_id], amount, [installment_number],
     *                    [razorpay_payment_id], [razorpay_order_id], [razorpay_signature],
     *                    [payment_status], [payment_date], [receipt_number], [payment_source], [notes]
     * @return int|false New payment ID, or false on failure
     */
    public function createPayment(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sudamaseva_payments
                (subscription_id, donor_id, amount, installment_number,
                 razorpay_payment_id, razorpay_order_id, razorpay_signature,
                 payment_status, payment_date, receipt_number, payment_source, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['subscription_id'] ?? null,
                $data['donor_id'] ?? null,
                $data['amount'],
                $data['installment_number'] ?? 0,
                $data['razorpay_payment_id'] ?? null,
                $data['razorpay_order_id'] ?? null,
                $data['razorpay_signature'] ?? null,
                $data['payment_status'] ?? 'created',
                $data['payment_date'] ?? date('Y-m-d H:i:s'),
                $data['receipt_number'] ?? null,
                $data['payment_source'] ?? 'subscription_charge',
                $data['notes'] ?? null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::createPayment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update payment status and optionally set Razorpay details.
     */
    public function updatePayment(int $id, array $data): bool
    {
        try {
            $allowedColumns = [
                'payment_status', 'razorpay_payment_id', 'razorpay_order_id',
                'razorpay_signature', 'payment_date', 'receipt_number', 'notes',
            ];

            $sets = [];
            $params = [];

            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns, true)) {
                    continue;
                }
                $sets[] = "`{$key}` = ?";
                $params[] = $value;
            }

            $params[] = $id;
            $stmt = $this->db->prepare(
                "UPDATE sudamaseva_payments SET " . implode(', ', $sets) . " WHERE id = ?"
            );

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::updatePayment error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment statistics.
     */
    public function getPaymentStats(?string $from = null, ?string $to = null): array
    {
        try {
            $where = ['1 = 1'];
            $countParams = [];

            if ($from) {
                $where[] = 'payment_date >= ?';
                $countParams[] = $from;
            }
            if ($to) {
                $where[] = 'payment_date < ?';
                $countParams[] = $to;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);

            // Total payment count (all statuses)
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM sudamaseva_payments {$whereClause}");
            $stmt->execute($countParams);
            $totalCount = (int) $stmt->fetchColumn();

            // Paid payment count
            $paidParams = array_merge(['paid'], $countParams);
            $paidWhere = "WHERE payment_status = ? AND " . substr($whereClause, 7); // remove leading '1 = 1 AND ' if needed
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM sudamaseva_payments {$paidWhere}");
            $stmt->execute($paidParams);
            $paidCount = (int) $stmt->fetchColumn();

            // Total amount from paid payments
            $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_payments {$paidWhere}");
            $stmt->execute($paidParams);
            $totalAmount = (int) $stmt->fetchColumn();

            return [
                'total_payments' => $totalCount,
                'paid_payments' => $paidCount,
                'total_amount' => $totalAmount,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentStats error: ' . $e->getMessage());
            return ['total_payments' => 0, 'paid_payments' => 0, 'total_amount' => 0];
        }
    }

    /**
     * Get the latest receipt sequence number for a given year.
     */
    public function getNextReceiptSequence(string $year = null): int
    {
        $year = $year ?? date('Y');
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(receipt_no, '/', -1) AS UNSIGNED)), 0) 
                FROM sudamaseva_receipts 
                WHERE receipt_no LIKE ?
            ");
            $stmt->execute(["SMS/{$year}/%"]);
            return (int) $stmt->fetchColumn() + 1;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getNextReceiptSequence error: ' . $e->getMessage());
            return 1; // Start from 1 if table is empty or error
        }
    }

    // ============================================================
    // RECEIPT QUERIES
    // ============================================================

    /**
     * Get a receipt by its ID (includes payment and donor details).
     */
    public function getReceiptById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, p.amount, p.payment_date, p.razorpay_payment_id,
                       d.donor_name, d.phone, d.email, d.pan, d.area, d.city, d.state
                FROM sudamaseva_receipts r
                JOIN sudamaseva_payments p ON r.payment_id = p.id
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE r.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceiptById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a receipt by its receipt number.
     */
    public function getReceiptByNumber(string $receiptNo): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, p.amount, p.payment_date, p.razorpay_payment_id,
                       d.donor_name, d.phone, d.email, d.pan
                FROM sudamaseva_receipts r
                JOIN sudamaseva_payments p ON r.payment_id = p.id
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE r.receipt_no = ?
                LIMIT 1
            ");
            $stmt->execute([$receiptNo]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceiptByNumber error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get receipts for a specific payment.
     */
    public function getReceiptsByPayment(int $paymentId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, d.donor_name, d.phone
                FROM sudamaseva_receipts r
                LEFT JOIN sudamaseva_payments p ON r.payment_id = p.id
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE r.payment_id = ?
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([$paymentId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceiptsByPayment error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get receipts for a donor (via payment records).
     */
    public function getReceiptsByDonor(int $donorId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, p.amount, p.payment_date, p.razorpay_payment_id
                FROM sudamaseva_receipts r
                JOIN sudamaseva_payments p ON r.payment_id = p.id
                WHERE p.donor_id = ?
                ORDER BY r.receipt_date DESC
            ");
            $stmt->execute([$donorId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceiptsByDonor error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * List/search receipts with optional filters.
     */
    public function getReceipts(?string $from = null, ?string $to = null, int $page = 1, int $perPage = 50): array
    {
        try {
            $where = [];
            $params = [];

            if ($from) {
                $where[] = "r.receipt_date >= ?";
                $params[] = $from;
            }
            if ($to) {
                $where[] = "r.receipt_date < ?";
                $params[] = $to;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $offset = max(0, ($page - 1) * $perPage);

            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM sudamaseva_receipts r {$whereClause}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = (int) ceil($total / $perPage);

            $stmt = $this->db->prepare("
                SELECT r.*, p.amount, p.payment_date, d.donor_name, d.phone
                FROM sudamaseva_receipts r
                JOIN sudamaseva_payments p ON r.payment_id = p.id
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                {$whereClause}
                ORDER BY r.receipt_date DESC
                LIMIT ? OFFSET ?
            ");
            $paramIndex = 1;
            foreach ($params as $pVal) {
                $stmt->bindValue($paramIndex++, $pVal, PDO::PARAM_STR);
            }
            $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
            $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'receipts' => $stmt->fetchAll(),
                'total' => $total,
                'pages' => $pages,
                'page' => $page,
                'per_page' => $perPage,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceipts error: ' . $e->getMessage());
            return ['receipts' => [], 'total' => 0, 'pages' => 0, 'page' => 1, 'per_page' => $perPage];
        }
    }

    /**
     * Create a receipt for a payment.
     *
     * @param array $data Keys: payment_id, receipt_no, [receipt_date], [receipt_data], [is_80g_eligible]
     * @return int|false New receipt ID, or false on failure
     */
    public function createReceipt(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sudamaseva_receipts
                (payment_id, receipt_no, receipt_date, receipt_data, is_80g_eligible, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $data['payment_id'],
                $data['receipt_no'],
                $data['receipt_date'] ?? date('Y-m-d H:i:s'),
                isset($data['receipt_data']) ? json_encode($data['receipt_data']) : null,
                $data['is_80g_eligible'] ?? 0,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::createReceipt error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get receipt statistics.
     */
    public function getReceiptStats(): array
    {
        try {
            $total = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_receipts")->fetchColumn();
            $eightyg = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_receipts WHERE is_80g_eligible = 1")->fetchColumn();
            $today = (int) $this->db->query("SELECT COUNT(*) FROM sudamaseva_receipts WHERE DATE(receipt_date) = CURDATE()")->fetchColumn();

            return [
                'total' => $total,
                'is_80g_eligible' => $eightyg,
                'generated_today' => $today,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getReceiptStats error: ' . $e->getMessage());
            return ['total' => 0, 'is_80g_eligible' => 0, 'generated_today' => 0];
        }
    }

    // ============================================================
    // DASHBOARD / REPORT QUERIES
    // ============================================================

    /**
     * Get a consolidated donor dashboard with subscription & payment summary.
     */
    public function getDonorDashboard(int $donorId): array
    {
        try {
            $donor = $this->getDonorById($donorId);
            if (!$donor) {
                return [];
            }

            $subscriptions = $this->getSubscriptionsByDonor($donorId);
            $recentPayments = $this->getPaymentsByDonor($donorId, 10);

            // Totals
            $totalPaid = 0;
            $lastPaymentDate = null;
            foreach ($subscriptions as $sub) {
                $totalPaid += $sub['amount'] * $sub['installments_paid'];
            }

            if (!empty($recentPayments)) {
                $lastPaymentDate = $recentPayments[0]['payment_date'];
            }

            $activeSub = null;
            foreach ($subscriptions as $sub) {
                if ($sub['status'] === 'active') {
                    $activeSub = $sub;
                    break;
                }
            }

            return [
                'donor' => $donor,
                'active_subscription' => $activeSub,
                'subscriptions' => $subscriptions,
                'recent_payments' => $recentPayments,
                'total_paid' => $totalPaid,
                'last_payment_date' => $lastPaymentDate,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDonorDashboard error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get yearly revenue grouped by Indian financial year (April-March).
     *
     * @return array Each row: {financial_year, fy_start_year, payment_count, total_amount}
     */
    public function getYearlyRevenue(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    CASE 
                        WHEN MONTH(payment_date) >= 4 
                        THEN CONCAT(YEAR(payment_date), '-', YEAR(payment_date) + 1)
                        ELSE CONCAT(YEAR(payment_date) - 1, '-', YEAR(payment_date))
                    END as financial_year,
                    CASE 
                        WHEN MONTH(payment_date) >= 4 
                        THEN YEAR(payment_date)
                        ELSE YEAR(payment_date) - 1
                    END as fy_start_year,
                    COUNT(*) AS payment_count,
                    SUM(amount) AS total_amount
                FROM sudamaseva_payments
                WHERE payment_status = 'paid'
                GROUP BY financial_year, fy_start_year
                ORDER BY fy_start_year ASC
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getYearlyRevenue error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly revenue for a specific financial year.
     * Used for year-over-year monthly comparison charts.
     *
     * @param int $fyStartYear The start year of the financial year (e.g., 2025 for FY 2025-2026)
     * @return array Each row: {month_num, month_name, total_amount, payment_count}
     */
    public function getYearlyMonthlyRevenue(int $fyStartYear): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    MONTH(payment_date) AS month_num,
                    DATE_FORMAT(payment_date, '%b') AS month_name,
                    SUM(amount) AS total_amount,
                    COUNT(*) AS payment_count
                FROM sudamaseva_payments
                WHERE payment_status = 'paid'
                  AND payment_date >= ?
                  AND payment_date < ?
                GROUP BY month_num, month_name
                ORDER BY month_num ASC
            ");

            $startDate = "{$fyStartYear}-04-01 00:00:00";
            $endDate = ($fyStartYear + 1) . "-03-31 23:59:59";
            $stmt->execute([$startDate, $endDate]);

            // Pad to all 12 months (April-March)
            $months = [];
            for ($i = 4; $i <= 12; $i++) {
                $months[$i] = [
                    'month_num' => $i,
                    'month_name' => date('M', mktime(0, 0, 0, $i, 1)),
                    'total_amount' => 0,
                    'payment_count' => 0,
                ];
            }
            for ($i = 1; $i <= 3; $i++) {
                $months[$i] = [
                    'month_num' => $i,
                    'month_name' => date('M', mktime(0, 0, 0, $i, 1)),
                    'total_amount' => 0,
                    'payment_count' => 0,
                ];
            }

            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $mn = (int) $row['month_num'];
                if (isset($months[$mn])) {
                    $months[$mn]['total_amount'] = (int) ($row['total_amount'] ?? 0);
                    $months[$mn]['payment_count'] = (int) ($row['payment_count'] ?? 0);
                }
            }

            // Re-order: April to March
            $ordered = [];
            for ($i = 4; $i <= 12; $i++) {
                $ordered[] = $months[$i];
            }
            for ($i = 1; $i <= 3; $i++) {
                $ordered[] = $months[$i];
            }

            return $ordered;
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getYearlyMonthlyRevenue error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent payments across all donors.
     */
    public function getRecentPayments(int $limit = 20): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, d.donor_name, d.phone, d.email
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                WHERE p.payment_status = 'paid'
                ORDER BY p.payment_date DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getRecentPayments error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly revenue data.
     *
     * @param int $months Number of past months to return
     * @return array Each row: {year, month, count, total}
     */
    public function getMonthlyRevenue(int $months = 12): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    YEAR(payment_date) AS year,
                    MONTH(payment_date) AS month,
                    COUNT(*) AS payment_count,
                    SUM(amount) AS total_amount
                FROM sudamaseva_payments
                WHERE payment_status = 'paid'
                  AND payment_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY YEAR(payment_date), MONTH(payment_date)
                ORDER BY year DESC, month DESC
            ");
            $stmt->execute([$months]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getMonthlyRevenue error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get overall dashboard statistics.
     */
    public function getDashboardStats(): array
    {
        try {
            $donorStats = $this->getDonorStats();
            $subStats = $this->getSubscriptionStats();

            // Revenue today
            $revenueToday = (int) $this->db->query("
                SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_payments 
                WHERE payment_status = 'paid' AND DATE(payment_date) = CURDATE()
            ")->fetchColumn();

            // Revenue this month
            $revenueMonth = (int) $this->db->query("
                SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_payments 
                WHERE payment_status = 'paid' 
                  AND YEAR(payment_date) = YEAR(NOW()) 
                  AND MONTH(payment_date) = MONTH(NOW())
            ")->fetchColumn();

            // Revenue all time
            $revenueAllTime = (int) $this->db->query("
                SELECT COALESCE(SUM(amount), 0) FROM sudamaseva_payments 
                WHERE payment_status = 'paid'
            ")->fetchColumn();

            // Subscriptions starting today
            $newSubsToday = (int) $this->db->query("
                SELECT COUNT(*) FROM sudamaseva_subscriptions 
                WHERE DATE(created_at) = CURDATE()
            ")->fetchColumn();

            // Payments pending receipts
            $pendingReceipts = (int) $this->db->query("
                SELECT COUNT(*) FROM sudamaseva_payments p
                WHERE p.payment_status = 'paid'
                  AND p.id NOT IN (SELECT payment_id FROM sudamaseva_receipts)
            ")->fetchColumn();

            return [
                'donors' => $donorStats,
                'subscriptions' => $subStats,
                'revenue_today' => $revenueToday,
                'revenue_this_month' => $revenueMonth,
                'revenue_all_time' => $revenueAllTime,
                'new_subs_today' => $newSubsToday,
                'pending_receipts' => $pendingReceipts,
            ];
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getDashboardStats error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a combined list of payments with donor info for export.
     */
    public function getPaymentsForExport(string $from, string $to): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.id, p.amount, p.installment_number, p.payment_status,
                    p.payment_date, p.receipt_number, p.razorpay_payment_id,
                    d.donor_name, d.phone, d.email, d.pan,
                    s.id as subscription_id, s.amount as monthly_amount,
                    r.receipt_no, r.receipt_date, r.is_80g_eligible
                FROM sudamaseva_payments p
                LEFT JOIN sudamaseva_donors d ON p.donor_id = d.id
                LEFT JOIN sudamaseva_subscriptions s ON p.subscription_id = s.id
                LEFT JOIN sudamaseva_receipts r ON r.payment_id = p.id
                WHERE p.payment_date >= ? AND p.payment_date < ?
                  AND p.payment_status = 'paid'
                ORDER BY p.payment_date ASC
            ");
            $stmt->execute([$from, $to]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('SudamasevaRepository::getPaymentsForExport error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Generate a UUID v4 string.
     */
    public function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
