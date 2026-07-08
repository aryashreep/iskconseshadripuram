<?php
/**
 * Donation Module — Route Registration
 * 
 * Define all routes owned by this module. This file is loaded by the Kernel
 * router to register public, API, and admin routes.
 * 
 * Route format:
 *   'url/path' => ['METHOD', 'HandlerClass', 'methodName', ['required_roles']]
 * 
 * Role array is optional — only needed for admin routes. Super_admin always has access.
 * 
 * @see modules/Kernel/Router.php for routing implementation
 */

return [
    'routes' => [
        // ============================================================
        // PUBLIC PAGES
        // ============================================================
        'donate/{slug}' => [
            'GET',
            'DonationController',
            'showCause',
        ],
        'donate/{slug}/checkout' => [
            'GET',
            'CheckoutController',
            'showCheckout',
        ],
        'donate/payment-success' => [
            'GET',
            'PaymentController',
            'showSuccess',
        ],
        'donate/payment-failed' => [
            'GET',
            'PaymentController',
            'showFailed',
        ],
        'checkout' => [
            'GET',
            'CheckoutController',
            'showCheckout',
        ],

        // ============================================================
        // API ENDPOINTS
        // ============================================================
        'api/create-order' => [
            'POST',
            'PaymentController',
            'createOrder',
        ],
        'api/verify-payment' => [
            'POST',
            'PaymentController',
            'verifyPayment',
        ],
        'api/webhook' => [
            'POST',
            'PaymentController',
            'handleWebhook',
        ],
        // TODO: Move to Booking module when created
        'api/create-booking-order' => [
            'POST',
            'PaymentController',
            'createBookingOrder',
        ],
        // TODO: Move to Pages module when created
        'api/track-view' => [
            'POST',
            'TrackingController',
            'trackView',
        ],

        // ============================================================
        // ADMIN PAGES
        // ============================================================
        'admin/donations' => [
            'GET',
            'Admin\\TransactionLogs',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/report-dashboard' => [
            'GET',
            'Admin\\Reports\\Dashboard',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/report-category' => [
            'GET',
            'Admin\\Reports\\CategoryReport',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/report-activity' => [
            'GET',
            'Admin\\Reports\\ActivityReport',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/report-seva' => [
            'GET',
            'Admin\\Reports\\SevaReport',
            'index',
            ['super_admin', 'treasurer'],
        ],
        'admin/seva-catalogue' => [
            'GET',
            'Admin\\SevaCatalogue',
            'index',
            ['super_admin', 'editor'],
        ],
        'admin/seva-catalogue-edit' => [
            'GET',
            'Admin\\SevaCatalogue',
            'edit',
            ['super_admin', 'editor'],
        ],
        'admin/export-donations' => [
            'GET',
            'Admin\\Exports\\DonationsExport',
            'export',
            ['super_admin', 'treasurer'],
        ],
        'admin/export-report-activity' => [
            'GET',
            'Admin\\Exports\\ActivityReportExport',
            'export',
            ['super_admin', 'treasurer'],
        ],
        'admin/export-report-category' => [
            'GET',
            'Admin\\Exports\\CategoryReportExport',
            'export',
            ['super_admin', 'treasurer'],
        ],
        'admin/export-report-seva' => [
            'GET',
            'Admin\\Exports\\SevaReportExport',
            'export',
            ['super_admin', 'treasurer'],
        ],
        'admin/ajax/master-sevas-by-category' => [
            'GET',
            'Admin\\Ajax\\MasterSevasByCategory',
            'get',
            ['super_admin', 'editor'],
        ],
    ],

    // ============================================================
    // ADMIN SIDEBAR MENU CONFIGURATION
    // ============================================================
    'menu' => [
        'label' => 'Donations',
        'icon' => 'fa-hand-holding-heart',
        'roles' => ['super_admin', 'treasurer'],
        'children' => [
            [
                'label' => 'Dashboard',
                'route' => 'admin/report-dashboard',
                'icon' => 'fa-chart-pie',
            ],
            [
                'label' => 'Transaction Logs',
                'route' => 'admin/donations',
                'icon' => 'fa-list',
            ],
            [
                'label' => 'Category Report',
                'route' => 'admin/report-category',
                'icon' => 'fa-layer-group',
            ],
            [
                'label' => 'Activity Report',
                'route' => 'admin/report-activity',
                'icon' => 'fa-calendar-alt',
            ],
            [
                'label' => 'Seva Report',
                'route' => 'admin/report-seva',
                'icon' => 'fa-ribbon',
            ],
        ],
    ],
];
