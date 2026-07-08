<?php
/**
 * Donation System - Helper Functions (Backward-Compatible Facade)
 * 
 * This file provides backward-compatible function wrappers around the new
 * class-based architecture. Existing code that calls these functions will
 * continue to work without modification.
 * 
 * NEW CODE should use the classes directly:
 *   use Isjm\Modules\Donation\DonationRepository;
 *   use Isjm\Modules\Donation\DonationService;
 *   use Isjm\Modules\Donation\DonationRenderer;
 */

use Isjm\Modules\Donation\DonationRepository;
use Isjm\Modules\Donation\DonationService;
use Isjm\Modules\Donation\DonationRenderer;

require_once __DIR__ . '/db.php';

// Lazy-load singleton instances
function _donationRepo(): DonationRepository {
    static $repo = null;
    if ($repo === null) {
        $repo = new DonationRepository();
    }
    return $repo;
}

function _donationService(): DonationService {
    static $service = null;
    if ($service === null) {
        $service = new DonationService(_donationRepo());
    }
    return $service;
}

function _donationRenderer(): DonationRenderer {
    static $renderer = null;
    if ($renderer === null) {
        $renderer = new DonationRenderer(_donationService());
    }
    return $renderer;
}

// ============================================================
// 1. CAUSE FETCHING
// ============================================================

function getDonationCauseBySlug(string $slug): ?array {
    return _donationRepo()->getCauseBySlug($slug);
}

function getDonationCauses(?string $category = null, bool $featuredOnly = false): array {
    return _donationRepo()->getCauses($category, $featuredOnly);
}

function getDonationCauseCategories(): array {
    return _donationRepo()->getCauseCategories();
}

function getDonationCausesGrouped(): array {
    return _donationService()->getCausesGrouped();
}

// ============================================================
// 2. MASTER SEVA CATALOG
// ============================================================

function hasMasterCatalogSevas(int $causeId): bool {
    return _donationRepo()->hasMasterCatalogSevas($causeId);
}

function getMasterSevaCategories(bool $onlyActive = true): array {
    return _donationRepo()->getMasterSevaCategories($onlyActive);
}

function getMasterSevas(?int $categoryId = null, bool $onlyActive = true): array {
    return _donationRepo()->getMasterSevas($categoryId, $onlyActive);
}

function getSevaCategories(): array {
    return _donationRepo()->getSevaCategories();
}

function getCauseSevasGrouped(int $causeId): array {
    return _donationRepo()->getCauseSevasGrouped($causeId);
}

function getCauseSevas(int $causeId): array {
    return _donationRepo()->getCauseSevas($causeId);
}

// ============================================================
// 2b. ADMIN HELPERS
// ============================================================

function getMasterCatalogForAdmin(): array {
    return _donationRepo()->getMasterCatalogForAdmin();
}

function getCauseLinkedMasterSevas(int $causeId): array {
    return _donationRepo()->getCauseLinkedMasterSevas($causeId);
}

// ============================================================
// 2c. MASTER CATALOG CRUD
// ============================================================

function getMasterSevaById(int $id): ?array {
    return _donationRepo()->getMasterSevaById($id);
}

function getMasterSevaCategoriesForSelect(): array {
    return _donationRepo()->getMasterSevaCategoriesForSelect();
}

function getMasterSevaUsageCount(int $masterSevaId): int {
    return _donationRepo()->getMasterSevaUsageCount($masterSevaId);
}

function getMasterSevasWithUsageByCategory(bool $includeInactive = false): array {
    return _donationRepo()->getMasterSevasWithUsageByCategory($includeInactive);
}

function createMasterSeva(array $data) {
    return _donationRepo()->createMasterSeva($data);
}

function updateMasterSeva(int $id, array $data): bool {
    return _donationRepo()->updateMasterSeva($id, $data);
}

function archiveMasterSeva(int $id, bool $hardDelete = false) {
    return _donationRepo()->archiveMasterSeva($id, $hardDelete);
}

function getCauseLinkedMasterSevasDetailed(int $causeId): array {
    return _donationRepo()->getCauseLinkedMasterSevasDetailed($causeId);
}

function getCauseOldSevas(int $causeId): array {
    return _donationRepo()->getCauseOldSevas($causeId);
}

// ============================================================
// 3. TRANSACTION & SUBSCRIPTION
// ============================================================

function createDonationTransaction(array $data) {
    return _donationRepo()->createDonationTransaction($data);
}

function updateDonationTransaction(string $orderId, array $data): bool {
    return _donationRepo()->updateDonationTransaction($orderId, $data);
}

// ============================================================
// 4. UTILITY FUNCTIONS
// ============================================================

function getCauseCategoryInfo(string $category): array {
    return _donationService()->getCategoryInfo($category);
}

function formatDonationAmount(float $amount): string {
    return _donationService()->formatAmount($amount);
}

function getCauseForPage(string $pageType, string $pageSlug): ?array {
    return _donationRepo()->getCauseForPage($pageType, $pageSlug);
}

// ============================================================
// 5. HOMEPAGE QUERY HELPERS
// ============================================================

function getHomepageServiceCauses(int $limit = 6): array {
    return _donationRepo()->getHomepageServiceCauses($limit);
}

function getHomepageFestivalCauses(int $limit = 6): array {
    return _donationRepo()->getHomepageFestivalCauses($limit);
}

function getSeasonalHomepageCause(?int $month = null): ?array {
    return _donationService()->getSeasonalCause($month);
}

function getSeasonalMonthLabel(?int $month = null): string {
    return _donationService()->getSeasonalMonthLabel($month);
}

function getHomepageCategoryTiles(): array {
    return _donationService()->getCategoryTiles();
}

// ============================================================
// 6. FESTIVAL CONTENT HELPERS
// ============================================================

function getFestivalDetail(string $slug): ?array {
    return _donationRepo()->getFestivalDetail($slug);
}

function getFestivalsByCategory(string $category, bool $timeBoundOnly = false): array {
    return _donationRepo()->getFestivalsByCategory($category, $timeBoundOnly);
}

function parseQuickStats(?string $stats): array {
    return _donationService()->parseQuickStats($stats);
}

// ============================================================
// 7. RENDER HELPERS (HTML snippets)
// ============================================================

function renderDonationCTA(array $options = []): void {
    _donationRenderer()->renderCTA($options);
}

function renderDonationSevaOptions(array $cause, array $groupedSevas, ?string $formTypeOverride = null): void {
    _donationRenderer()->renderSevaOptions($cause, $groupedSevas, $formTypeOverride);
}

function getDefaultCauseAmount(array $cause): float {
    return _donationRepo()->getDefaultCauseAmount($cause);
}

function getRelatedCauses(array $cause, int $limit = 4): array {
    return _donationRepo()->getRelatedCauses($cause, $limit);
}

function createPujaBooking(array $data) {
    return _donationRepo()->createPujaBooking($data);
}
