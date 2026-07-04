<?php
/**
 * Migration: Seed Digital Master Sevas
 *
 * Adds 18 Digital sevas to the Master Seva Catalog (category_id=9):
 * 1.  Temple Website Development
 * 2.  Website Maintenance
 * 3.  Website Hosting
 * 4.  Domain Renewal
 * 5.  SSL Certificate
 * 6.  Website Security
 * 7.  Devotee Care Application
 * 8.  Mobile App Development
 * 9.  Donation Platform Maintenance
 * 10. Live Streaming Infrastructure
 * 11. Cloud Infrastructure
 * 12. Server Maintenance
 * 13. Data Backup
 * 14. Cyber Security
 * 15. Email Infrastructure
 * 16. SMS / WhatsApp Notification Service
 * 17. Social Media Outreach
 * 18. E-Learning Development
 *
 * Idempotent — safe to re-run.
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/donation-helpers.php';

$db = getDB();
echo "=== Seeding Digital Master Sevas ===\n\n";

// Step 1: Verify category exists
$catStmt = $db->prepare("SELECT id, name FROM master_seva_categories WHERE slug = 'digital-sevas' AND is_active = 1");
$catStmt->execute();
$category = $catStmt->fetch();

if (!$category) {
    echo "ERROR: Digital Sevas category not found!\n";
    exit(1);
}
echo "[OK] Category found: {$category['name']} (id={$category['id']})\n";
$category_id = (int)$category['id'];

// Step 2: Define the 18 Digital sevas
$digitalSevas = [
    [
        'slug'              => 'temple-website-development',
        'name'              => 'Temple Website Development',
        'default_amount'    => 5008,
        'description'       => 'Sponsor the development and design of the temple website. A well-crafted website serves as a digital gateway for devotees worldwide to connect with the temple, access information, and participate in sevas online.',
        'short_description' => 'Sponsor temple website creation',
        'icon'              => 'fa-code',
        'sort_order'        => 10,
    ],
    [
        'slug'              => 'website-maintenance',
        'name'              => 'Website Maintenance',
        'default_amount'    => 2008,
        'description'       => 'Support the ongoing maintenance, updates, bug fixes, and improvements of the temple website to ensure a smooth and reliable experience for all visitors and devotees.',
        'short_description' => 'Keep the website running smoothly',
        'icon'              => 'fa-tools',
        'sort_order'        => 20,
    ],
    [
        'slug'              => 'website-hosting',
        'name'              => 'Website Hosting',
        'default_amount'    => 3008,
        'description'       => 'Sponsor the annual hosting costs for the temple website. Reliable hosting ensures the website stays online 24/7, serving thousands of devotees and visitors around the clock.',
        'short_description' => 'Keep the website always online',
        'icon'              => 'fa-server',
        'sort_order'        => 30,
    ],
    [
        'slug'              => 'domain-renewal',
        'name'              => 'Domain Renewal',
        'default_amount'    => 1008,
        'description'       => 'Sponsor the annual renewal of the temple\'s domain name. This ensures the temple\'s digital identity remains secure and accessible to devotees worldwide.',
        'short_description' => 'Secure the temple\'s digital identity',
        'icon'              => 'fa-globe',
        'sort_order'        => 40,
    ],
    [
        'slug'              => 'ssl-certificate',
        'name'              => 'SSL Certificate',
        'default_amount'    => 1008,
        'description'       => 'Sponsor the SSL certificate that encrypts and secures all data on the temple website. This protects devotee information during donations and registrations.',
        'short_description' => 'Secure devotee data and transactions',
        'icon'              => 'fa-lock',
        'sort_order'        => 50,
    ],
    [
        'slug'              => 'website-security',
        'name'              => 'Website Security',
        'default_amount'    => 2008,
        'description'       => 'Support advanced security measures for the temple website including firewalls, malware scanning, DDoS protection, and regular security audits to protect against cyber threats.',
        'short_description' => 'Protect the website from cyber threats',
        'icon'              => 'fa-shield-alt',
        'sort_order'        => 60,
    ],
    [
        'slug'              => 'devotee-care-application',
        'name'              => 'Devotee Care Application',
        'default_amount'    => 5008,
        'description'       => 'Sponsor the development of a dedicated Devotee Care Application that helps manage devotee records, events, seva bookings, and community engagement in one unified platform.',
        'short_description' => 'Build the devotee care platform',
        'icon'              => 'fa-users-cog',
        'sort_order'        => 70,
    ],
    [
        'slug'              => 'mobile-app-development',
        'name'              => 'Mobile App Development',
        'default_amount'    => 10008,
        'description'       => 'Sponsor the development of a mobile application for the temple, enabling devotees to access darshan schedules, make donations, book sevas, watch live streams, and stay connected on the go.',
        'short_description' => 'Build the temple mobile app',
        'icon'              => 'fa-mobile-alt',
        'sort_order'        => 80,
    ],
    [
        'slug'              => 'donation-platform-maintenance',
        'name'              => 'Donation Platform Maintenance',
        'default_amount'    => 3008,
        'description'       => 'Support the maintenance and upgrades of the online donation platform, ensuring secure payment processing, reliable transaction handling, and smooth donor experience.',
        'short_description' => 'Keep donations secure and reliable',
        'icon'              => 'fa-hand-holding-usd',
        'sort_order'        => 90,
    ],
    [
        'slug'              => 'live-streaming-infrastructure',
        'name'              => 'Live Streaming Infrastructure',
        'default_amount'    => 5008,
        'description'       => 'Sponsor the live streaming setup including cameras, encoding equipment, streaming servers, and bandwidth to broadcast temple programs, aratis, and festivals to devotees worldwide.',
        'short_description' => 'Broadcast temple programs globally',
        'icon'              => 'fa-broadcast-tower',
        'sort_order'        => 100,
    ],
    [
        'slug'              => 'cloud-infrastructure',
        'name'              => 'Cloud Infrastructure',
        'default_amount'    => 5008,
        'description'       => 'Support cloud hosting infrastructure for the temple\'s digital services. Cloud servers provide scalable, reliable, and high-performance hosting for websites, apps, and streaming.',
        'short_description' => 'Scalable cloud hosting for temple services',
        'icon'              => 'fa-cloud',
        'sort_order'        => 110,
    ],
    [
        'slug'              => 'server-maintenance',
        'name'              => 'Server Maintenance',
        'default_amount'    => 3008,
        'description'       => 'Sponsor regular server maintenance including hardware checks, software updates, performance optimization, and security patches to keep all temple digital services running smoothly.',
        'short_description' => 'Keep servers healthy and optimized',
        'icon'              => 'fa-cogs',
        'sort_order'        => 120,
    ],
    [
        'slug'              => 'data-backup',
        'name'              => 'Data Backup',
        'default_amount'    => 2008,
        'description'       => 'Support automated backup systems that protect the temple\'s digital data including devotee records, donation history, media files, and website content from data loss.',
        'short_description' => 'Protect temple data from loss',
        'icon'              => 'fa-database',
        'sort_order'        => 130,
    ],
    [
        'slug'              => 'cyber-security',
        'name'              => 'Cyber Security',
        'default_amount'    => 5008,
        'description'       => 'Sponsor comprehensive cyber security measures for the temple\'s digital presence including penetration testing, incident response, threat monitoring, and security training.',
        'short_description' => 'Defend temple digital assets',
        'icon'              => 'fa-user-shield',
        'sort_order'        => 140,
    ],
    [
        'slug'              => 'email-infrastructure',
        'name'              => 'Email Infrastructure',
        'default_amount'    => 2008,
        'description'       => 'Sponsor the temple\'s email infrastructure for newsletters, event notifications, donation receipts, and devotee communication. Reliable email keeps the community connected.',
        'short_description' => 'Keep devotee communication flowing',
        'icon'              => 'fa-envelope',
        'sort_order'        => 150,
    ],
    [
        'slug'              => 'sms-whatsapp-notification',
        'name'              => 'SMS / WhatsApp Notification Service',
        'default_amount'    => 3008,
        'description'       => 'Support SMS and WhatsApp notification services for sending event reminders, donation confirmations, festival alerts, and important temple announcements to devotees instantly.',
        'short_description' => 'Instant notifications to devotees',
        'icon'              => 'fa-sms',
        'sort_order'        => 160,
    ],
    [
        'slug'              => 'social-media-outreach',
        'name'              => 'Social Media Outreach',
        'default_amount'    => 2008,
        'description'       => 'Sponsor social media campaigns, content creation, and digital marketing to spread Krishna consciousness across platforms like YouTube, Instagram, Facebook, and Twitter.',
        'short_description' => 'Spread Krishna consciousness online',
        'icon'              => 'fa-share-alt',
        'sort_order'        => 170,
    ],
    [
        'slug'              => 'e-learning-development',
        'name'              => 'E-Learning Development',
        'default_amount'    => 5008,
        'description'       => 'Sponsor the development of online courses, video lectures, and interactive learning modules on Vedic scriptures, Bhagavad-gita, Srimad Bhagavatam, and devotional practices.',
        'short_description' => 'Online courses on Vedic wisdom',
        'icon'              => 'fa-graduation-cap',
        'sort_order'        => 180,
    ],
];

// Step 3: Insert/update sevas
$createdCount = 0;
$updatedCount = 0;

foreach ($digitalSevas as $sData) {
    $checkStmt = $db->prepare("SELECT id FROM master_sevas WHERE slug = ?");
    $checkStmt->execute([$sData['slug']]);
    $existingId = $checkStmt->fetchColumn();

    $data = [
        'slug'              => $sData['slug'],
        'name'              => $sData['name'],
        'category_id'       => $category_id,
        'default_amount'    => $sData['default_amount'],
        'description'       => $sData['description'],
        'short_description' => $sData['short_description'] ?? null,
        'icon'              => $sData['icon'] ?? 'fa-laptop-code',
        'allow_multiple'    => 1,
        'max_quantity'      => 99,
        'sort_order'        => $sData['sort_order'],
        'is_active'         => 1,
    ];

    if ($existingId) {
        if (updateMasterSeva((int)$existingId, $data)) {
            $updatedCount++;
            echo "  [UPDATE] {$sData['name']} (id={$existingId})\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — update failed\n";
        }
    } else {
        $newId = createMasterSeva($data);
        if ($newId) {
            $createdCount++;
            echo "  [CREATE] {$sData['name']} (id={$newId}) — ₹" . number_format($sData['default_amount']) . "\n";
        } else {
            echo "  [FAIL]   {$sData['name']} — create failed\n";
        }
    }
}

echo "\nSummary: {$createdCount} created, {$updatedCount} updated\n";

// Step 4: Link to general-donation cause if it exists
$linkedCount = 0;
$causeSlugs = ['general-donation'];

foreach ($causeSlugs as $causeSlug) {
    $causeStmt = $db->prepare("SELECT id, slug, title FROM donation_causes WHERE slug = ? AND is_active = 1 LIMIT 1");
    $causeStmt->execute([$causeSlug]);
    $cause = $causeStmt->fetch();

    if (!$cause) continue;

    echo "\n[OK] Found cause: {$cause['title']} (slug={$cause['slug']})\n";

    $sevaStmt = $db->prepare("SELECT id, name FROM master_sevas WHERE category_id = ? AND is_active = 1 ORDER BY sort_order");
    $sevaStmt->execute([$category_id]);
    $sevas = $sevaStmt->fetchAll();

    $causeId = (int)$cause['id'];
    $upsertStmt = $db->prepare("
        INSERT INTO donation_cause_master_sevas
        (cause_id, master_seva_id, override_amount, override_description, override_max_quantity, sort_order, is_featured, is_active)
        VALUES (?, ?, NULL, NULL, NULL, ?, 0, 1)
        ON DUPLICATE KEY UPDATE
            sort_order = VALUES(sort_order),
            is_active = 1
    ");

    foreach ($sevas as $i => $seva) {
        try {
            $sOrder = ($i + 1) * 10;
            $upsertStmt->execute([$causeId, $seva['id'], $sOrder]);
            $linkedCount++;
        } catch (PDOException $e) {
            echo "  [LINK FAIL] {$seva['name']}: {$e->getMessage()}\n";
        }
    }
}

if ($linkedCount > 0) {
    echo "\nLinked {$linkedCount} seva-cause relationships\n";
} else {
    echo "\n[SKIP] No matching causes found — sevas not linked to any cause.\n";
}

echo "\n=== Done! ===\n";
