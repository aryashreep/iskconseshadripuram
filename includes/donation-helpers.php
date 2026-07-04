<?php
/**
 * Donation System - Helper Functions
 * 
 * Provides reusable functions for fetching causes, rendering forms, 
 * and generating CTA buttons across festival/service pages.
 */

require_once __DIR__ . '/db.php';

// ============================================================
// 1. CAUSE FETCHING
// ============================================================

/**
 * Get a donation cause by its slug
 * 
 * @param string $slug
 * @return array|null
 */
function getDonationCauseBySlug(string $slug): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $cause = $stmt->fetch();
        return $cause ?: null;
    } catch (PDOException $e) {
        error_log('getDonationCauseBySlug error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get all active donation causes, optionally filtered by category
 * 
 * @param string|null $category e.g. 'festival', 'service', 'general'
 * @param bool $featuredOnly Only return featured causes
 * @return array
 */
function getDonationCauses(?string $category = null, bool $featuredOnly = false): array {
    try {
        $db = getDB();
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
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getDonationCauses error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all cause categories (distinct)
 * 
 * @return array
 */
function getDonationCauseCategories(): array {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT category FROM donation_causes WHERE is_active = 1 ORDER BY FIELD(category, 'festival','ekadashi','appearance','disappearance','event','service','construction','general')");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        error_log('getDonationCauseCategories error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Group causes by category for display
 * 
 * @return array ['festival' => [...], 'service' => [...], ...]
 */
function getDonationCausesGrouped(): array {
    $causes = getDonationCauses();
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

// ============================================================
// 2. MASTER SEVA CATALOG (Phase 2 — Dual Read)
//    Reads from new master_sevas + donation_cause_master_sevas tables
//    Falls back to old donation_seva_categories + donation_cause_sevas
//    for backward compatibility during transition.
// ============================================================

/**
 * Check if the Master Seva Catalog tables exist and have data for a cause
 * 
 * @param int $causeId
 * @return bool
 */
function hasMasterCatalogSevas(int $causeId): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM donation_cause_master_sevas WHERE cause_id = ? AND is_active = 1");
        $stmt->execute([$causeId]);
        return (int)$stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Get all active master seva categories (10 umbrella categories)
 * 
 * @param bool $onlyActive
 * @return array
 */
function getMasterSevaCategories(bool $onlyActive = true): array {
    try {
        $db = getDB();
        $sql = "SELECT * FROM master_seva_categories";
        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getMasterSevaCategories error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all active master sevas from the catalog
 * 
 * @param int|null $categoryId Filter by category (optional)
 * @param bool $onlyActive
 * @return array
 */
function getMasterSevas(?int $categoryId = null, bool $onlyActive = true): array {
    try {
        $db = getDB();
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
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getMasterSevas error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get all active old-style seva categories (legacy)
 * 
 * @return array
 */
function getSevaCategories(): array {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM donation_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getSevaCategories error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get sevas (pricing tiers) for a specific cause, grouped by category
 * 
 * Phase 2 Dual-Read Strategy:
 *   1. Try the new master catalog tables (donation_cause_master_sevas + master_sevas)
 *   2. If no data found, fall back to old tables (donation_cause_sevas + donation_seva_categories)
 * 
 * @param int $causeId
 * @return array [category_slug => ['category' => [...], 'items' => [...]]]
 */
function getCauseSevasGrouped(int $causeId): array {
    try {
        $db = getDB();
        
        // Strategy 1: Read from new Master Catalog tables
        if (hasMasterCatalogSevas($causeId)) {
            $sql = "
                SELECT 
                    dcms.id,
                    dcms.cause_id,
                    dcms.master_seva_id,
                    msc.id as category_id,
                    ms.name,
                    ms.sanskrit_name,
                    COALESCE(dcms.override_amount, ms.default_amount) as amount,
                    COALESCE(dcms.override_description, ms.description) as description,
                    dcms.is_featured,
                    dcms.sort_order,
                    msc.slug as cat_slug,
                    msc.name as cat_name,
                    msc.sanskrit_name as cat_sanskrit,
                    msc.icon as cat_icon,
                    msc.sort_order as cat_sort,
                    ms.allow_multiple,
                    COALESCE(dcms.override_max_quantity, ms.max_quantity) as max_quantity,
                    ms.image_url
                FROM donation_cause_master_sevas dcms
                JOIN master_sevas ms ON dcms.master_seva_id = ms.id
                JOIN master_seva_categories msc ON ms.category_id = msc.id
                WHERE dcms.cause_id = ? AND dcms.is_active = 1 AND ms.is_active = 1
                ORDER BY msc.sort_order ASC, dcms.sort_order ASC, ms.name ASC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$causeId]);
            $rows = $stmt->fetchAll();
        } else {
            // Strategy 2: Fallback to old tables
            $sql = "SELECT cs.*, sc.slug as cat_slug, sc.name as cat_name, sc.sanskrit_name as cat_sanskrit,
                           sc.icon as cat_icon, sc.sort_order as cat_sort
                    FROM donation_cause_sevas cs
                    JOIN donation_seva_categories sc ON cs.category_id = sc.id
                    WHERE cs.cause_id = ?
                    ORDER BY sc.sort_order ASC, cs.sort_order ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$causeId]);
            $rows = $stmt->fetchAll();
            
            if (!empty($rows)) {
                error_log("DEPRECATED: Cause #{$causeId} is using old donation_cause_sevas table. " .
                    "Migrate to Master Seva Catalog: run the Phase 1 migration or assign sevas via admin.");
            }
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

/**
 * Get all sevas (pricing tiers) for a specific cause (flat list)
 * 
 * Phase 2 Dual-Read Strategy:
 *   1. Try the new master catalog tables
 *   2. If no data found, fall back to old tables
 * 
 * @param int $causeId
 * @return array
 */
function getCauseSevas(int $causeId): array {
    try {
        $db = getDB();
        
        // Strategy 1: Read from new Master Catalog tables
        if (hasMasterCatalogSevas($causeId)) {
            $stmt = $db->prepare("
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
            // Strategy 2: Fallback to old tables
            $stmt = $db->prepare(
                "SELECT cs.*, sc.name as cat_name, sc.slug as cat_slug, sc.icon as cat_icon
                 FROM donation_cause_sevas cs
                 JOIN donation_seva_categories sc ON cs.category_id = sc.id
                 WHERE cs.cause_id = ?
                 ORDER BY cs.sort_order ASC"
            );
            $stmt->execute([$causeId]);
            
            if ($stmt->rowCount() > 0) {
                error_log("DEPRECATED: Cause #{$causeId} is using old donation_cause_sevas table. " .
                    "Migrate to Master Seva Catalog.");
            }
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getCauseSevas error: ' . $e->getMessage());
        return [];
    }
}

// ============================================================
// 2b. ADMIN HELPERS (Phase 3 — Master Catalog Admin Picker)
// ============================================================

/**
 * Get the full Master Catalog organized by category for the admin picker UI
 * 
 * @return array [ [category => [...], items => [...]], ... ]
 */
function getMasterCatalogForAdmin(): array {
    try {
        $db = getDB();
        $categories = $db->query("
            SELECT * FROM master_seva_categories 
            WHERE is_active = 1 
            ORDER BY sort_order ASC
        ")->fetchAll();
        
        $catalog = [];
        foreach ($categories as $cat) {
            $stmt = $db->prepare("
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

/**
 * Get the master sevas currently linked to a cause, indexed by master_seva_id
 * 
 * @param int $causeId
 * @return array [ master_seva_id => [ link data ], ... ]
 */
function getCauseLinkedMasterSevas(int $causeId): array {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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

// ============================================================
// 2c. MASTER CATALOG ADMIN CRUD HELPERS (Phase — Seva Catalogue)
// ============================================================

/**
 * Get a single master seva by ID with its category info
 * 
 * @param int $id
 * @return array|null
 */
function getMasterSevaById(int $id): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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

/**
 * Get active master seva categories for dropdown select
 * 
 * @return array
 */
function getMasterSevaCategoriesForSelect(): array {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT id, name, icon FROM master_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getMasterSevaCategoriesForSelect error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get count of active cause links for a master seva
 * 
 * @param int $masterSevaId
 * @return int
 */
function getMasterSevaUsageCount(int $masterSevaId): int {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM donation_cause_master_sevas WHERE master_seva_id = ? AND is_active = 1");
        $stmt->execute([$masterSevaId]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log('getMasterSevaUsageCount error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Get master sevas grouped by category with usage counts, for admin listing
 * 
 * @param bool $includeInactive Whether to include inactive sevas
 * @return array [ [category => [...], items => [...]], ... ]
 */
function getMasterSevasWithUsageByCategory(bool $includeInactive = false): array {
    try {
        $db = getDB();
        $categories = $db->query("SELECT * FROM master_seva_categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();
        
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
            
            $stmt = $db->prepare($sql);
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

/**
 * Create a new master seva
 * 
 * @param array $data
 * @return int|false The new ID or false on failure
 */
function createMasterSeva(array $data) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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
        
        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log('createMasterSeva error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update an existing master seva
 * 
 * @param int $id
 * @param array $data
 * @return bool
 */
function updateMasterSeva(int $id, array $data): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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

/**
 * Archive (soft-delete) a master seva by setting is_active = 0
 * 
 * @param int $id
 * @param bool $hardDelete If true, delete permanently (only if unused)
 * @return bool|string True on success, error message string on failure
 */
function archiveMasterSeva(int $id, bool $hardDelete = false) {
    try {
        $db = getDB();
        
        $usageCount = getMasterSevaUsageCount($id);
        
        if ($hardDelete && $usageCount === 0) {
            $stmt = $db->prepare("DELETE FROM master_sevas WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        }
        
        // Soft delete
        $stmt = $db->prepare("UPDATE master_sevas SET is_active = 0 WHERE id = ?");
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

/**
 * Get cause-linked master sevas with full details (seva + category + link overrides)
 * Uses a single JOIN query to avoid N+1 lookups.
 * 
 * @param int $causeId
 * @return array
 */
function getCauseLinkedMasterSevasDetailed(int $causeId): array {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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

/**
 * Get old-style sevas for a cause (legacy read for admin display)
 * 
 * @param int $causeId
 * @return array
 */
function getCauseOldSevas(int $causeId): array {
    try {
        $db = getDB();
        $stmt = $db->prepare("
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
// 3. TRANSACTION & SUBSCRIPTION
// ============================================================

/**
 * Create a new donation transaction record
 * 
 * @param array $data
 * @return int|false The transaction ID or false on failure
 */
function createDonationTransaction(array $data) {
    try {
        $db = getDB();
        $stmt = $db->prepare(
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
        
        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log('createDonationTransaction error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update transaction with Razorpay payment details
 * 
 * @param string $orderId
 * @param array $data
 * @return bool
 */
function updateDonationTransaction(string $orderId, array $data): bool {
    try {
        $db = getDB();
        $sets = [];
        $params = [];

        // Whitelist of allowed column names to prevent SQL column injection
        $allowedColumns = [
            'razorpay_payment_id', 'razorpay_signature', 'payment_status',
            'donor_name', 'donor_email', 'donor_phone', 'donor_address',
            'pan_number', 'amount', 'notes', 'metadata_json',
            'cause_id', 'seva_id', 'quantity', 'source_type', 'source_slug', 'source_url'
        ];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedColumns, true)) {
                continue; // Skip disallowed columns
            }
            $sets[] = "`$key` = ?";
            $params[] = $value;
        }
        
        $params[] = $orderId;
        $stmt = $db->prepare(
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
// 4. UTILITY FUNCTIONS
// ============================================================

/**
 * Get category display name (for UI section labels)
 * 
 * @param string $category
 * @return array ['label' => string, 'icon' => string]
 */
function getCauseCategoryInfo(string $category): array {
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
 * Format amount in Indian currency format
 * 
 * @param float $amount
 * @return string e.g. "₹1,00,008"
 */
function formatDonationAmount(float $amount): string {
    $negative = $amount < 0;
    $amount = abs($amount);
    
    $whole = floor($amount);
    $decimal = round(($amount - $whole) * 100);
    
    // Indian numbering: last 3 digits, then groups of 2
    $wholeStr = (string) $whole;
    $len = strlen($wholeStr);
    
    if ($len <= 3) {
        $formatted = $wholeStr;
    } else {
        $last3 = substr($wholeStr, -3);
        $rest = substr($wholeStr, 0, $len - 3);
        // Group rest in 2's
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
    
    $result = CURRENCY_SYMBOL . $formatted;
    if ($decimal > 0) {
        $result .= '.' . str_pad((string) $decimal, 2, '0', STR_PAD_LEFT);
    }
    
    return $negative ? '-' . $result : $result;
}

/**
 * Get the cause from a page context (lookup by page_type + page_slug)
 * 
 * @param string $pageType e.g. 'festival', 'service'
 * @param string $pageSlug e.g. 'janmashtami', 'daily-seva'
 * @return array|null
 */
function getCauseForPage(string $pageType, string $pageSlug): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT * FROM donation_causes 
             WHERE page_type = ? AND page_slug = ? AND is_active = 1 
             LIMIT 1"
        );
        $stmt->execute([$pageType, $pageSlug]);
        $cause = $stmt->fetch();
        
        // Fallback: try by slug
        if (!$cause) {
            return getDonationCauseBySlug($pageSlug);
        }
        
        return $cause ?: null;
    } catch (PDOException $e) {
        error_log('getCauseForPage error: ' . $e->getMessage());
        return null;
    }
}

// ============================================================
// 5. HOMEPAGE QUERY HELPERS
// ============================================================

/**
 * Get service/construction/general causes for the homepage "Ways to Serve" section
 * 
 * @param int $limit
 * @return array
 */
function getHomepageServiceCauses(int $limit = 6): array {
    try {
        $db = getDB();
        $stmt = $db->prepare(
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

/**
 * Get festival causes for the homepage "Featured Festivals" section
 * Excludes umbrella/fund causes that aren't individual festival pages
 * 
 * @param int $limit
 * @return array
 */
function getHomepageFestivalCauses(int $limit = 6): array {
    try {
        $db = getDB();
        $stmt = $db->prepare(
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

/**
 * Get a seasonal spotlight cause based on the current month
 * Uses a curated month-to-slug map (no real dates in the DB yet)
 * 
 * @param int|null $month Override month for testing
 * @return array|null
 */
function getSeasonalHomepageCause(?int $month = null): ?array {
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
    return getDonationCauseBySlug($slug);
}

/**
 * Returns the display month name for the seasonal spotlight
 * 
 * @param int|null $month
 * @return string
 */
function getSeasonalMonthLabel(?int $month = null): string {
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
 * Get category tile data for the "Explore" section
 * 
 * @return array
 */
function getHomepageCategoryTiles(): array {
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
// 6. FESTIVAL CONTENT HELPERS
// ============================================================

/**
 * Get festival detail content from donation_causes including full content_body
 * 
 * @param string $slug The festival slug
 * @return array|null Festival data with all content fields
 */
function getFestivalDetail(string $slug): ?array {
    try {
        $db = getDB();
        $stmt = $db->prepare(
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

/**
 * Get festivals by category from donation_causes
 * 
 * @param string $category e.g. 'festival', 'appearance', 'disappearance', 'ekadashi', 'event'
 * @param bool $timeBoundOnly Only return is_time_bound = 1
 * @return array
 */
function getFestivalsByCategory(string $category, bool $timeBoundOnly = false): array {
    try {
        $db = getDB();
        $sql = "SELECT slug, title, short_title, description, image_url, significance, 
                       featured, sort_order, is_time_bound
                FROM donation_causes
                WHERE is_active = 1 AND category = ?";
        $params = [$category];
        
        if ($timeBoundOnly) {
            $sql .= " AND is_time_bound = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, title ASC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('getFestivalsByCategory error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Parse quick_stats string into an associative array for display
 * Format: "Key: Value|Key2: Value2|Key3: Value3"
 * 
 * @param string|null $stats
 * @return array
 */
function parseQuickStats(?string $stats): array {
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
// 7. RENDER HELPERS (HTML snippets)
// ============================================================

/**
 * Render a donation CTA button for a specific cause
 * Use this on festival, service, and event pages.
 * 
 * Usage in festival pages:
 *   <?php renderDonationCTA(['cause_slug' => 'janmashtami', 'label' => 'Offer Seva for Janmashtami']); ?>
 * 
 * @param array $options
 *   - cause_slug: string (required)
 *   - label: string (default: "Donate Now")
 *   - mode: 'one_time'|'monthly' (default: 'one_time')
 *   - button_style: 'primary'|'secondary'|'outline' (default: 'primary')
 *   - size: 'sm'|'lg' (default: 'sm')
 *   - icon: string (default: 'fa-heart')
 *   - source_type: string (optional, for tracking)
 *   - source_slug: string (optional, for tracking)
 */
function renderDonationCTA(array $options = []): void {
    $slug = $options['cause_slug'] ?? '';
    $label = $options['label'] ?? 'Donate Now';
    $mode = $options['mode'] ?? 'one_time';
    $btnStyle = $options['button_style'] ?? 'primary';
    $size = $options['size'] ?? 'sm';
    $icon = $options['icon'] ?? 'fa-heart';
    
    $url = BASE_URL . 'donate/' . urlencode($slug);
    if ($mode === 'monthly') {
        $url .= '&mode=monthly';
    }
    
    $baseClass = "btn btn-{$btnStyle} btn-{$size}";
    ?>
    <a href="<?= htmlspecialchars($url) ?>" 
       class="<?= $baseClass ?>" 
       style="display:inline-flex; align-items:center; gap:6px; text-decoration:none;"
       data-cause="<?= htmlspecialchars($slug) ?>"
       data-mode="<?= htmlspecialchars($mode) ?>">
        <i class="fas <?= htmlspecialchars($icon) ?>"></i>
        <?= htmlspecialchars($label) ?>
    </a>
    <?php
}

/**
 * Render a full donation section with grouped sevas for a cause
 * This renders the entire "Offer Seva" card on the donate page.
 * 
 * @param array $cause
 * @param array $groupedSevas
 */
function renderDonationSevaOptions(array $cause, array $groupedSevas, ?string $formTypeOverride = null): void {
    $preview = isset($cause['is_preview']) && $cause['is_preview'];
    $formType = $formTypeOverride ?? $cause['form_type'] ?? 'tiers';
    ?>
    <div class="donation-options">
        <?php if ($formType === 'quantity'): ?>
            <!-- Quantity-based form (e.g., Shastra Daan) -->
            <div class="quantity-form">
                <?php foreach ($groupedSevas as $group): ?>
                    <?php foreach ($group['items'] as $item): ?>
                        <div class="quantity-item" data-seva-id="<?= $item['id'] ?>" data-per-unit="<?= $item['amount'] ?>">
                            <div class="quantity-item-info">
                                <span class="quantity-item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="quantity-item-price"><?= formatDonationAmount((float)$item['amount']) ?>/unit</span>
                            </div>
                            <div class="quantity-item-input">
                                <label>Qty:</label>
                                <input type="number" class="qty-input" min="0" max="1000" value="0" data-price="<?= $item['amount'] ?>">
                                <span class="qty-total">= <?= formatDonationAmount(0) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <div class="quantity-grand-total">
                    <span>Total:</span>
                    <span class="grand-total-amount"><?= formatDonationAmount(0) ?></span>
                </div>
            </div>
            
        <?php elseif ($formType === 'multi_item'): ?>
            <!-- Multi-item cart form (e.g., Tula Daan) -->
            <div class="multi-item-form">
                <?php foreach ($groupedSevas as $group): ?>
                    <?php foreach ($group['items'] as $item): ?>
                        <div class="cart-item" data-seva-id="<?= $item['id'] ?>" data-rate="<?= $item['amount'] ?>">
                            <div class="cart-item-header">
                                <span class="cart-item-name"><?= htmlspecialchars($item['name']) ?></span>
                                <span class="cart-item-rate"><?= formatDonationAmount((float)$item['amount']) ?>/kg</span>
                            </div>
                            <div class="cart-item-input">
                                <label>Weight (kg):</label>
                                <input type="number" class="cart-qty" min="0" max="10000" step="0.5" value="0" 
                                       data-rate="<?= $item['amount'] ?>" 
                                       data-seva-id="<?= $item['id'] ?>" 
                                       data-name="<?= htmlspecialchars($item['name']) ?>">
                                <span class="cart-item-total"><?= formatDonationAmount(0) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <div class="cart-grand-total">
                    <span>Grand Total:</span>
                    <span class="cart-grand-total-amount"><?= formatDonationAmount(0) ?></span>
                </div>
            </div>
            
        <?php elseif ($formType === 'cart' || $formType === 'cart_qty'): ?>
            <!-- Add to Cart with +/- Quantity Buttons -->
            <div class="donation-cart-qty-form">
                <?php $itemIndex = 0; ?>
                <?php foreach ($groupedSevas as $catSlug => $group): 
                    $cat = $group['category'];
                ?>
                    <?php if ($cat['name']): ?>
                    <div class="seva-category-label">
                        <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php foreach ($group['items'] as $item): 
                        $itemIndex++;
                        $maxQty = ($item['max_quantity'] ?? 0) > 0 ? $item['max_quantity'] : 99;
                    ?>
                    <div class="cart-qty-item" data-seva-id="<?= $item['id'] ?>" data-price="<?= (float)$item['amount'] ?>" data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>" data-max="<?= $maxQty ?>">
                        <div class="cart-qty-info">
                            <div class="cart-qty-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if ($item['description']): ?>
                            <div class="cart-qty-desc"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="cart-qty-controls">
                            <div class="cart-qty-price">₹<?= number_format((float)$item['amount']) ?></div>
                            <div class="qty-selector">
                                <button type="button" class="qty-btn qty-minus">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="qty-count" id="qty-count-<?= $item['id'] ?>">0</span>
                                <button type="button" class="qty-btn qty-plus">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="cart-qty-line-total" id="line-total-<?= $item['id'] ?>">₹0</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                
                <!-- Cart Summary -->
                <div class="cart-qty-summary" id="cartQtySummary" style="display:none;">
                    <div class="cart-qty-summary-header">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Selected Sevas</span>
                        <span class="cart-qty-total-items" id="cartTotalItems">0 items</span>
                    </div>
                    <div class="cart-qty-summary-items" id="cartSummaryItems">
                        <!-- Dynamically populated -->
                    </div>
                    <div class="cart-qty-grand-total">
                        <span>Total Donation</span>
                        <span class="cart-grand-amount" id="cartGrandAmount">₹0</span>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Tiered radio options (default) -->
            <div class="amount-options" id="amountOptions">
                <?php 
                $first = true;
                foreach ($groupedSevas as $catSlug => $group): 
                    $cat = $group['category'];
                ?>
                    <?php if ($cat['name']): ?>
                    <div class="seva-category-label">
                        <i class="fas <?= htmlspecialchars($cat['icon']) ?>"></i>
                        <?= htmlspecialchars($cat['name']) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php foreach ($group['items'] as $item): 
                        $active = $first ? 'active' : '';
                        $first = false;
                    ?>
                    <div class="amount-option <?= $active ?>"
                         data-amount="<?= (int)$item['amount'] ?>"
                         data-seva-id="<?= $item['id'] ?>"
                         onclick="selectDonationOption(this)">
                        <div class="amount-option-radio"></div>
                        <div class="amount-option-content">
                            <div class="amount-option-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if ($item['description']): ?>
                            <div class="amount-option-desc"><?= htmlspecialchars($item['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="amount-option-price"><?= formatDonationAmount((float)$item['amount']) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                
                <!-- Custom Amount -->
                <div class="custom-amount-row" onclick="toggleCustomAmount()">
                    <div class="plus-icon"><i class="fas fa-plus"></i></div>
                    <span>Custom Amount</span>
                </div>
                <div class="custom-amount-input-wrap" id="customAmountWrap">
                    <label for="customAmount">Enter your amount</label>
                    <div class="input-group">
                        <span class="input-currency">₹</span>
                        <input type="number" id="customAmount" min="<?= (int)$cause['min_amount'] ?>" 
                               max="1000000" placeholder="Enter amount" step="1">
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Get the default amount for a cause (first seva option or min_amount)
 * Supports dual-read: new master catalog first, then fallback to old tables.
 * 
 * @param array $cause
 * @return float
 */
function getDefaultCauseAmount(array $cause): float {
    try {
        $db = getDB();
        $causeId = $cause['id'] ?? 0;
        
        // Try new Master Catalog tables first
        if (hasMasterCatalogSevas($causeId)) {
            $stmt = $db->prepare("
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
                return (float)$row['amount'];
            }
        }
        
        // Fallback to old tables
        $stmt = $db->prepare(
            "SELECT amount FROM donation_cause_sevas WHERE cause_id = ? ORDER BY sort_order ASC LIMIT 1"
        );
        $stmt->execute([$causeId]);
        $row = $stmt->fetch();
        return $row ? (float)$row['amount'] : (float)$cause['min_amount'];
    } catch (PDOException $e) {
        return (float)$cause['min_amount'];
    }
}

/**
 * Get related causes for a cause (same category)
 * 
 * @param array $cause
 * @param int $limit
 * @return array
 */
function getRelatedCauses(array $cause, int $limit = 4): array {
    $causes = getDonationCauses($cause['category']);
    return array_slice(array_filter($causes, function($c) use ($cause) {
        return $c['id'] !== $cause['id'];
    }), 0, $limit);
}

/**
 * Create a new Puja booking record
 * 
 * @param array $data
 * @return int|false The booking ID or false on failure
 */
function createPujaBooking(array $data) {
    try {
        $db = getDB();
        $stmt = $db->prepare(
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
        
        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log('createPujaBooking error: ' . $e->getMessage());
        return false;
    }
}
