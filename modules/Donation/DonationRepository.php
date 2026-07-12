<?php

namespace Isjm\Modules\Donation;

use PDO;
use PDOException;

/**
 * Repository for all donation-related database operations.
 * 
 * Handles: causes, master sevas, transactions, bookings.
 */
class DonationRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? getDB();
    }

    // ============================================================
    // CAUSE QUERIES
    // ============================================================

    public function getCauseBySlug(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$slug]);
            $cause = $stmt->fetch();
            return $cause ?: null;
        } catch (PDOException $e) {
            error_log('getCauseBySlug error: ' . $e->getMessage());
            return null;
        }
    }

    public function getCauses(?string $category = null, bool $featuredOnly = false): array
    {
        try {
            $sql = "SELECT * FROM donation_causes WHERE is_active = 1";
            $params = [];

            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            if ($featuredOnly) {
                $sql .= " AND featured = 1";
            }

            $sql .= " ORDER BY sort_order ASC, title ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getCauses error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseCategories(): array
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT category FROM donation_causes WHERE is_active = 1 ORDER BY FIELD(category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general')");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log('getCauseCategories error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseForPage(string $pageType, string $pageSlug): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM donation_causes 
                 WHERE page_type = ? AND page_slug = ? AND is_active = 1 
                 LIMIT 1"
            );
            $stmt->execute([$pageType, $pageSlug]);
            $cause = $stmt->fetch();

            if (!$cause) {
                return $this->getCauseBySlug($pageSlug);
            }

            return $cause ?: null;
        } catch (PDOException $e) {
            error_log('getCauseForPage error: ' . $e->getMessage());
            return null;
        }
    }

    public function getRelatedCauses(array $cause, int $limit = 4): array
    {
        $causes = $this->getCauses($cause['category']);
        return array_slice(array_filter($causes, function ($c) use ($cause) {
            return $c['id'] !== $cause['id'];
        }), 0, $limit);
    }

    // ============================================================
    // MASTER SEVA CATALOG QUERIES
    // ============================================================

    public function hasMasterCatalogSevas(int $causeId): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM donation_cause_master_sevas WHERE cause_id = ? AND is_active = 1");
            $stmt->execute([$causeId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getMasterSevaCategories(bool $onlyActive = true): array
    {
        try {
            $sql = "SELECT * FROM master_seva_categories";
            if ($onlyActive) {
                $sql .= " WHERE is_active = 1";
            }
            $sql .= " ORDER BY sort_order ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getMasterSevaCategories error: ' . $e->getMessage());
            return [];
        }
    }

    public function getMasterSevas(?int $categoryId = null, bool $onlyActive = true): array
    {
        try {
            $sql = "SELECT ms.*, msc.name as cat_name, msc.slug as cat_slug, msc.icon as cat_icon
                    FROM master_sevas ms
                    JOIN master_seva_categories msc ON ms.category_id = msc.id";
            $params = [];
            $where = [];

            if ($onlyActive) {
                $where[] = "ms.is_active = 1";
            }
            if ($categoryId !== null) {
                $where[] = "ms.category_id = ?";
                $params[] = $categoryId;
            }

            if (!empty($where)) {
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            $sql .= " ORDER BY msc.sort_order ASC, ms.sort_order ASC, ms.name ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getMasterSevas error: ' . $e->getMessage());
            return [];
        }
    }

    public function getMasterSevaById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT ms.*, msc.name as cat_name, msc.slug as cat_slug, msc.icon as cat_icon
                FROM master_sevas ms
                JOIN master_seva_categories msc ON ms.category_id = msc.id
                WHERE ms.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('getMasterSevaById error: ' . $e->getMessage());
            return null;
        }
    }

    public function getMasterSevaCategoriesForSelect(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, name, icon FROM master_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getMasterSevaCategoriesForSelect error: ' . $e->getMessage());
            return [];
        }
    }

    public function getMasterSevaUsageCount(int $masterSevaId): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM donation_cause_master_sevas WHERE master_seva_id = ? AND is_active = 1");
            $stmt->execute([$masterSevaId]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('getMasterSevaUsageCount error: ' . $e->getMessage());
            return 0;
        }
    }

    public function getMasterSevasWithUsageByCategory(bool $includeInactive = false): array
    {
        try {
            $categories = $this->db->query("SELECT * FROM master_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

            $catalog = [];
            foreach ($categories as $cat) {
                $sql = "
                    SELECT ms.*, 
                           COUNT(dcms.id) as usage_count
                    FROM master_sevas ms
                    LEFT JOIN donation_cause_master_sevas dcms ON ms.id = dcms.master_seva_id AND dcms.is_active = 1
                    WHERE ms.category_id = ?";

                $params = [$cat['id']];

                if (!$includeInactive) {
                    $sql .= " AND ms.is_active = 1";
                }

                $sql .= " GROUP BY ms.id ORDER BY ms.sort_order ASC, ms.name ASC";

                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
                $sevas = $stmt->fetchAll();

                $catalog[] = [
                    'category' => $cat,
                    'items' => $sevas,
                ];
            }

            return $catalog;
        } catch (PDOException $e) {
            error_log('getMasterSevasWithUsageByCategory error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseLinkedMasterSevas(int $causeId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM donation_cause_master_sevas 
                WHERE cause_id = ? AND is_active = 1
            ");
            $stmt->execute([$causeId]);
            $rows = $stmt->fetchAll();

            $indexed = [];
            foreach ($rows as $row) {
                $indexed[$row['master_seva_id']] = $row;
            }

            return $indexed;
        } catch (PDOException $e) {
            error_log('getCauseLinkedMasterSevas error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseLinkedMasterSevasDetailed(int $causeId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    dcms.id as link_id,
                    dcms.master_seva_id,
                    dcms.override_amount,
                    dcms.override_description,
                    dcms.override_max_quantity,
                    dcms.sort_order,
                    dcms.is_featured,
                    dcms.is_active as link_active,
                    ms.id as seva_id,
                    ms.name as seva_name,
                    ms.description as seva_description,
                    ms.short_description,
                    ms.default_amount,
                    ms.allow_multiple,
                    ms.max_quantity,
                    ms.icon as seva_icon,
                    msc.id as category_id,
                    msc.name as cat_name,
                    msc.slug as cat_slug,
                    msc.icon as cat_icon
                FROM donation_cause_master_sevas dcms
                JOIN master_sevas ms ON dcms.master_seva_id = ms.id
                JOIN master_seva_categories msc ON ms.category_id = msc.id
                WHERE dcms.cause_id = ? AND dcms.is_active = 1
                ORDER BY dcms.sort_order ASC, ms.name ASC
            ");
            $stmt->execute([$causeId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getCauseLinkedMasterSevasDetailed error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseSevas(int $causeId): array
    {
        try {
            if ($this->hasMasterCatalogSevas($causeId)) {
                $stmt = $this->db->prepare("
                    SELECT 
                        dcms.id,
                        dcms.cause_id,
                        dcms.master_seva_id,
                        msc.id as category_id,
                        ms.name,
                        COALESCE(dcms.override_amount, ms.default_amount) as amount,
                        COALESCE(dcms.override_description, ms.description) as description,
                        dcms.is_featured,
                        dcms.sort_order,
                        msc.name as cat_name,
                        msc.slug as cat_slug,
                        msc.icon as cat_icon,
                        ms.allow_multiple,
                        COALESCE(dcms.override_max_quantity, ms.max_quantity) as max_quantity,
                        ms.image_url
                    FROM donation_cause_master_sevas dcms
                    JOIN master_sevas ms ON dcms.master_seva_id = ms.id
                    JOIN master_seva_categories msc ON ms.category_id = msc.id
                    WHERE dcms.cause_id = ? AND dcms.is_active = 1 AND ms.is_active = 1
                    ORDER BY dcms.sort_order ASC, ms.name ASC
                ");
                $stmt->execute([$causeId]);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT cs.*, sc.name as cat_name, sc.slug as cat_slug, sc.icon as cat_icon
                     FROM donation_cause_sevas cs
                     JOIN donation_seva_categories sc ON cs.category_id = sc.id
                     WHERE cs.cause_id = ?
                     ORDER BY cs.sort_order ASC"
                );
                $stmt->execute([$causeId]);

                if ($stmt->rowCount() > 0) {
                    error_log("DEPRECATED: Cause #{$causeId} is using old donation_cause_sevas table.");
                }
            }

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getCauseSevas error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseSevasGrouped(int $causeId): array
    {
        try {
            if ($this->hasMasterCatalogSevas($causeId)) {
                $sql = "
                    SELECT 
                        dcms.id, dcms.cause_id, dcms.master_seva_id,
                        msc.id as category_id, ms.name, ms.sanskrit_name,
                        COALESCE(dcms.override_amount, ms.default_amount) as amount,
                        COALESCE(dcms.override_description, ms.description) as description,
                        dcms.is_featured, dcms.sort_order,
                        msc.slug as cat_slug, msc.name as cat_name, msc.sanskrit_name as cat_sanskrit,
                        msc.icon as cat_icon, msc.sort_order as cat_sort,
                        ms.allow_multiple,
                        COALESCE(dcms.override_max_quantity, ms.max_quantity) as max_quantity,
                        ms.image_url
                    FROM donation_cause_master_sevas dcms
                    JOIN master_sevas ms ON dcms.master_seva_id = ms.id
                    JOIN master_seva_categories msc ON ms.category_id = msc.id
                    WHERE dcms.cause_id = ? AND dcms.is_active = 1 AND ms.is_active = 1
                    ORDER BY msc.sort_order ASC, dcms.sort_order ASC, ms.name ASC
                ";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([$causeId]);
                $rows = $stmt->fetchAll();
            } else {
                $sql = "SELECT cs.*, sc.slug as cat_slug, sc.name as cat_name, sc.sanskrit_name as cat_sanskrit,
                               sc.icon as cat_icon, sc.sort_order as cat_sort
                        FROM donation_cause_sevas cs
                        JOIN donation_seva_categories sc ON cs.category_id = sc.id
                        WHERE cs.cause_id = ?
                        ORDER BY sc.sort_order ASC, cs.sort_order ASC";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([$causeId]);
                $rows = $stmt->fetchAll();
            }

            $grouped = [];
            foreach ($rows as $row) {
                $catSlug = $row['cat_slug'];
                if (!isset($grouped[$catSlug])) {
                    $grouped[$catSlug] = [
                        'category' => [
                            'slug' => $row['cat_slug'],
                            'name' => $row['cat_name'],
                            'sanskrit' => $row['cat_sanskrit'] ?? '',
                            'icon' => $row['cat_icon'],
                        ],
                        'items' => [],
                    ];
                }
                $grouped[$catSlug]['items'][] = $row;
            }

            return $grouped;
        } catch (PDOException $e) {
            error_log('getCauseSevasGrouped error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDefaultCauseAmount(array $cause): float
    {
        try {
            $causeId = $cause['id'] ?? 0;

            if ($this->hasMasterCatalogSevas($causeId)) {
                $stmt = $this->db->prepare("
                    SELECT COALESCE(dcms.override_amount, ms.default_amount) as amount
                    FROM donation_cause_master_sevas dcms
                    JOIN master_sevas ms ON dcms.master_seva_id = ms.id
                    WHERE dcms.cause_id = ? AND dcms.is_active = 1 AND ms.is_active = 1
                    ORDER BY dcms.sort_order ASC
                    LIMIT 1
                ");
                $stmt->execute([$causeId]);
                $row = $stmt->fetch();
                if ($row) {
                    return (float) $row['amount'];
                }
            }

            $stmt = $this->db->prepare(
                "SELECT amount FROM donation_cause_sevas WHERE cause_id = ? ORDER BY sort_order ASC LIMIT 1"
            );
            $stmt->execute([$causeId]);
            $row = $stmt->fetch();
            return $row ? (float) $row['amount'] : (float) $cause['min_amount'];
        } catch (PDOException $e) {
            return (float) $cause['min_amount'];
        }
    }

    // ============================================================
    // HOMEPAGE QUERIES
    // ============================================================

    public function getHomepageServiceCauses(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT slug, title, short_title, description, image_url, significance, featured, sort_order
                 FROM donation_causes
                 WHERE is_active = 1
                   AND category IN ('service', 'construction', 'general')
                 ORDER BY featured DESC, sort_order ASC
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getHomepageServiceCauses error: ' . $e->getMessage());
            return [];
        }
    }

    public function getHomepageFestivalCauses(int $limit = 6): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT slug, title, short_title, description, image_url, significance, featured, sort_order
                 FROM donation_causes
                 WHERE is_active = 1
                   AND category = 'festival'
                   AND is_time_bound = 1
                 ORDER BY featured DESC, sort_order ASC
                 LIMIT ?"
            );
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getHomepageFestivalCauses error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // FESTIVAL QUERIES
    // ============================================================

    public function getFestivalDetail(string $slug): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT slug, title, short_title, description, history, significance, benefits,
                        image_url, category, subcategory, is_time_bound, start_date, end_date,
                        content_body, quick_stats, meta_title, meta_description
                 FROM donation_causes
                 WHERE slug = ? AND is_active = 1
                 LIMIT 1"
            );
            $stmt->execute([$slug]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('getFestivalDetail error: ' . $e->getMessage());
            return null;
        }
    }

    public function getFestivalsByCategory(string $category, bool $timeBoundOnly = false): array
    {
        try {
            $sql = "SELECT slug, title, short_title, description, image_url, significance, 
                           featured, sort_order, is_time_bound
                    FROM donation_causes
                    WHERE is_active = 1 AND category = ?";
            $params = [$category];

            if ($timeBoundOnly) {
                $sql .= " AND is_time_bound = 1";
            }

            $sql .= " ORDER BY sort_order ASC, title ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getFestivalsByCategory error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // SEVA CATEGORIES (Legacy)
    // ============================================================

    public function getSevaCategories(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM donation_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getSevaCategories error: ' . $e->getMessage());
            return [];
        }
    }

    public function getCauseOldSevas(int $causeId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT cs.*, sc.name as cat_name 
                FROM donation_cause_sevas cs
                JOIN donation_seva_categories sc ON cs.category_id = sc.id
                WHERE cs.cause_id = ?
                ORDER BY cs.sort_order ASC, cs.id ASC
            ");
            $stmt->execute([$causeId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getCauseOldSevas error: ' . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // MASTER CATALOG CRUD
    // ============================================================

    public function getMasterCatalogForAdmin(): array
    {
        try {
            $categories = $this->db->query("
                SELECT * FROM master_seva_categories 
                WHERE is_active = 1 
                ORDER BY sort_order ASC
            ")->fetchAll();

            $catalog = [];
            foreach ($categories as $cat) {
                $stmt = $this->db->prepare("
                    SELECT * FROM master_sevas 
                    WHERE category_id = ? AND is_active = 1
                    ORDER BY sort_order ASC, name ASC
                ");
                $stmt->execute([$cat['id']]);
                $sevas = $stmt->fetchAll();

                $catalog[] = [
                    'category' => $cat,
                    'items' => $sevas,
                ];
            }

            return $catalog;
        } catch (PDOException $e) {
            error_log('getMasterCatalogForAdmin error: ' . $e->getMessage());
            return [];
        }
    }

    public function createMasterSeva(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO master_sevas (
                    slug, name, sanskrit_name, description, short_description,
                    category_id, default_amount, min_amount, max_amount,
                    image_url, icon, allow_multiple, max_quantity,
                    is_featured, is_active, is_time_bound,
                    available_from, available_until, sort_order
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['slug'],
                $data['name'],
                $data['sanskrit_name'] ?? null,
                $data['description'] ?? null,
                $data['short_description'] ?? null,
                $data['category_id'],
                $data['default_amount'] ?? 0,
                $data['min_amount'] ?? null,
                $data['max_amount'] ?? null,
                $data['image_url'] ?? null,
                $data['icon'] ?? 'fa-hand-holding-heart',
                $data['allow_multiple'] ?? 0,
                $data['max_quantity'] ?? 1,
                $data['is_featured'] ?? 0,
                $data['is_active'] ?? 1,
                $data['is_time_bound'] ?? 0,
                $data['available_from'] ?? null,
                $data['available_until'] ?? null,
                $data['sort_order'] ?? 0,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('createMasterSeva error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateMasterSeva(int $id, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE master_sevas SET
                    slug = ?, name = ?, sanskrit_name = ?, description = ?, short_description = ?,
                    category_id = ?, default_amount = ?, min_amount = ?, max_amount = ?,
                    image_url = ?, icon = ?, allow_multiple = ?, max_quantity = ?,
                    is_featured = ?, is_active = ?, is_time_bound = ?,
                    available_from = ?, available_until = ?, sort_order = ?
                WHERE id = ?
            ");

            return $stmt->execute([
                $data['slug'],
                $data['name'],
                $data['sanskrit_name'] ?? null,
                $data['description'] ?? null,
                $data['short_description'] ?? null,
                $data['category_id'],
                $data['default_amount'] ?? 0,
                $data['min_amount'] ?? null,
                $data['max_amount'] ?? null,
                $data['image_url'] ?? null,
                $data['icon'] ?? 'fa-hand-holding-heart',
                $data['allow_multiple'] ?? 0,
                $data['max_quantity'] ?? 1,
                $data['is_featured'] ?? 0,
                $data['is_active'] ?? 1,
                $data['is_time_bound'] ?? 0,
                $data['available_from'] ?? null,
                $data['available_until'] ?? null,
                $data['sort_order'] ?? 0,
                $id,
            ]);
        } catch (PDOException $e) {
            error_log('updateMasterSeva error: ' . $e->getMessage());
            return false;
        }
    }

    public function archiveMasterSeva(int $id, bool $hardDelete = false): true|string
    {
        try {
            $usageCount = $this->getMasterSevaUsageCount($id);

            if ($hardDelete && $usageCount === 0) {
                $stmt = $this->db->prepare("DELETE FROM master_sevas WHERE id = ?");
                $stmt->execute([$id]);
                return true;
            }

            $stmt = $this->db->prepare("UPDATE master_sevas SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);

            if ($usageCount > 0) {
                return "Seva archived. It was linked to {$usageCount} cause(s) — those links are preserved but the seva is hidden from public pages.";
            }

            return true;
        } catch (PDOException $e) {
            error_log('archiveMasterSeva error: ' . $e->getMessage());
            return 'Failed to archive seva: ' . $e->getMessage();
        }
    }

    // ============================================================
    // TRANSACTIONS
    // ============================================================

    public function createDonationTransaction(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO donation_transactions 
                 (cause_id, seva_id, master_seva_id, donor_name, donor_email, donor_phone, donor_address, pan_number,
                  amount, currency, donation_mode, quantity, source_type, source_slug, source_url,
                  razorpay_order_id, payment_status, notes, metadata_json)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $data['cause_id'] ?? null,
                $data['seva_id'] ?? null,
                $data['master_seva_id'] ?? null,
                $data['donor_name'] ?? '',
                $data['donor_email'] ?? null,
                $data['donor_phone'] ?? null,
                $data['donor_address'] ?? null,
                $data['pan_number'] ?? null,
                $data['amount'] ?? 0,
                $data['currency'] ?? 'INR',
                $data['donation_mode'] ?? 'one_time',
                $data['quantity'] ?? 1,
                $data['source_type'] ?? null,
                $data['source_slug'] ?? null,
                $data['source_url'] ?? null,
                $data['razorpay_order_id'] ?? null,
                $data['payment_status'] ?? 'created',
                $data['notes'] ?? null,
                isset($data['metadata']) ? json_encode($data['metadata']) : null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('createDonationTransaction error: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDonationTransaction(string $orderId, array $data): bool
    {
        try {
            $sets = [];
            $params = [];

            $allowedColumns = [
                'razorpay_payment_id', 'razorpay_signature', 'payment_status',
                'donor_name', 'donor_email', 'donor_phone', 'donor_address',
                'pan_number', 'amount', 'notes', 'metadata_json',
                'cause_id', 'seva_id', 'quantity', 'source_type', 'source_slug', 'source_url'
            ];

            foreach ($data as $key => $value) {
                if (!in_array($key, $allowedColumns, true)) {
                    continue;
                }
                $sets[] = "`$key` = ?";
                $params[] = $value;
            }

            $params[] = $orderId;
            $stmt = $this->db->prepare(
                "UPDATE donation_transactions SET " . implode(', ', $sets) . " 
                 WHERE razorpay_order_id = ?"
            );

            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('updateDonationTransaction error for order ' . $orderId . ': ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // PUJA BOOKINGS
    // ============================================================

    public function createPujaBooking(array $data): int|false
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO booking_pujas 
                 (transaction_id, puja_type, puja_date, occasion, person_name, gotra, rashi, nakshatra, special_instructions)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $data['transaction_id'] ?? null,
                $data['puja_type'] ?? '',
                $data['puja_date'] ?? '',
                $data['occasion'] ?? null,
                $data['person_name'] ?? '',
                $data['gotra'] ?? null,
                $data['rashi'] ?? null,
                $data['nakshatra'] ?? null,
                $data['special_instructions'] ?? null,
            ]);

            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log('createPujaBooking error: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // TRANSACTION DETAIL (for admin detail page)
    // ============================================================

    /**
     * Get a single donation transaction by ID with joined cause/seva details.
     * Used by the admin donation detail page.
     */
    public function getTransactionById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT t.*,
                       c.title as cause_title,
                       c.slug as cause_slug,
                       c.category as cause_category,
                       COALESCE(ms.name, s.name) as seva_name,
                       ms.slug as master_seva_slug,
                       msc.name as seva_category_name
                FROM donation_transactions t
                LEFT JOIN donation_causes c ON t.cause_id = c.id
                LEFT JOIN master_sevas ms ON t.master_seva_id = ms.id
                LEFT JOIN master_seva_categories msc ON ms.category_id = msc.id
                LEFT JOIN donation_cause_sevas s ON t.seva_id = s.id
                WHERE t.id = ?
                LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('DonationRepository::getTransactionById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the puja booking associated with a donation transaction, if any.
     */
    public function getPujaBookingByTransactionId(int $transactionId): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM booking_pujas WHERE transaction_id = ? LIMIT 1");
            $stmt->execute([$transactionId]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('DonationRepository::getPujaBookingByTransactionId error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a subscription by ID, used to display linked subscription info on the donation detail page.
     */
    public function getSubscriptionById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM donation_subscriptions WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            error_log('DonationRepository::getSubscriptionById error: ' . $e->getMessage());
            return null;
        }
    }
}
