<?php
/**
 * Sudamaseva Module — Route Registration
 *
 * Define all routes owned by this module. Loaded by the Kernel router
 * when implemented. For now, routing is handled via .htaccess rewrite rules.
 *
 * @see modules/Donation/routes.php for reference pattern
 * @see .htaccess for active rewrite rules
 */

return [
    'routes' => [
        // ============================================================
        // PUBLIC CONTENT PAGES (future)
        // ============================================================
        'sudamaseva' => [
            'GET',
            'SudamasevaController',
            'index',
        ],
        'sudamaseva/subscribe' => [
            'GET',
            'SudamasevaController',
            'subscribe',
        ],
        'sudamaseva/success' => [
            'GET',
            'SudamasevaController',
            'success',
        ],
        'sudamaseva/lookup' => [
            'GET',
            'SudamasevaController',
            'lookup',
        ],
        'sudamaseva/dashboard' => [
            'GET',
            'SudamasevaController',
            'dashboard',
        ],

        // ============================================================
        // API ENDPOINTS
        // ============================================================
        'api/sudamaseva/create-subscription' => [
            'POST',
            'Api\\SubscriptionController',
            'create',
        ],
        'api/sudamaseva/create-order' => [
            'POST',
            'Api\\OrderController',
            'create',
        ],
        'api/sudamaseva/verify-order' => [
            'POST',
            'Api\\OrderController',
            'verify',
        ],
        'api/sudamaseva/lookup' => [
            'POST',
            'Api\\DonorController',
            'lookup',
        ],
        'api/sudamaseva/enroll' => [
            'POST',
            'Api\\EnrollController',
            'create',
        ],
        'api/sudamaseva/verify-payment' => [
            'POST',
            'Api\\SubscriptionController',
            'verifyPayment',
        ],
        'api/sudamaseva/webhook' => [
            'POST',
            'Api\\WebhookController',
            'handle',
        ],
        'api/sudamaseva/record-offline-payment' => [
            'POST',
            'Api\\RecordOfflinePayment',
            'create',
            ['super_admin', 'treasurer'],
        ],

        // ============================================================
        // ADMIN PAGES
        // ============================================================
        'admin/sudamaseva-dashboard' => [
            'GET',
            'Admin\\Dashboard',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-donors' => [
            'GET',
            'Admin\\Donors',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-subscriptions' => [
            'GET',
            'Admin\\Subscriptions',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-payments' => [
            'GET',
            'Admin\\Payments',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-receipts' => [
            'GET',
            'Admin\\Receipts',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-record-payment' => [
            'GET',
            'Admin\\RecordPayment',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/sudamaseva-export-payments' => [
            'GET',
            'Admin\\Exports\\PaymentsExport',
            'export',
            ['super_admin', 'treasurer'],
        ],
    ],

    // ============================================================
    // ADMIN SIDEBAR MENU CONFIGURATION
    // ============================================================
    'menu' => [
        'label' => 'Sudamaseva',
        'icon' => 'fa-sync',
        'roles' => ['super_admin', 'treasurer'],
        'children' => [
            [
                'label' => 'Dashboard',
                'route' => 'admin/sudamaseva-dashboard',
                'icon' => 'fa-chart-pie',
            ],
            [
                'label' => 'Donors',
                'route' => 'admin/sudamaseva-donors',
                'icon' => 'fa-users',
            ],
            [
                'label' => 'Subscriptions',
                'route' => 'admin/sudamaseva-subscriptions',
                'icon' => 'fa-sync',
            ],
            [
                'label' => 'Payments',
                'route' => 'admin/sudamaseva-payments',
                'icon' => 'fa-list',
            ],
            [
                'label' => 'Receipts',
                'route' => 'admin/sudamaseva-receipts',
                'icon' => 'fa-receipt',
            ],
            [
                'label' => 'Record Payment',
                'route' => 'admin/sudamaseva-record-payment',
                'icon' => 'fa-hand-holding-usd',
            ],
        ],
    ],
];
