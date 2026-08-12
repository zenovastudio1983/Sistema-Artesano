<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ERP General Configuration
    |--------------------------------------------------------------------------
    */
    'currency' => env('ERP_CURRENCY', 'ARS'),
    'currency_symbol' => env('ERP_CURRENCY_SYMBOL', '$'),
    'decimal_places' => (int) env('ERP_DECIMAL_PLACES', 2),
    'tax_rate' => (float) env('ERP_TAX_RATE', 21),

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    */
    'company' => [
        'name' => env('ERP_COMPANY_NAME', 'Mi Taller Artesanal'),
        'cuit' => env('ERP_COMPANY_CUIT', ''),
        'address' => env('ERP_COMPANY_ADDRESS', ''),
        'phone' => env('ERP_COMPANY_PHONE', ''),
        'email' => env('ERP_COMPANY_EMAIL', ''),
        'logo' => env('ERP_COMPANY_LOGO', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tienda Pública (Storefront)
    |--------------------------------------------------------------------------
    | Número de WhatsApp para recibir pedidos. Solo dígitos, con código de país.
    | Ej: 5491123456789 para Argentina +54 9 11 2345-6789
    */
    'whatsapp_number' => env('WHATSAPP_NUMBER', ''),

    /*
    |--------------------------------------------------------------------------
    | Cost Methods
    |--------------------------------------------------------------------------
    | average   - Costo promedio ponderado
    | standard  - Costo estándar (predefinido)
    | real      - Costo real (FIFO)
    */
    'cost_method' => env('ERP_COST_METHOD', 'average'),

    /*
    |--------------------------------------------------------------------------
    | Inventory Alerts
    |--------------------------------------------------------------------------
    */
    'low_stock_alert_days' => (int) env('ERP_LOW_STOCK_ALERT_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Order Number Formats
    |--------------------------------------------------------------------------
    */
    'formats' => [
        'purchase_order' => 'OC-{year}-{number:5}',
        'production_order' => 'OP-{year}-{number:5}',
        'sale' => 'VTA-{year}-{number:5}',
        'quotation' => 'COT-{year}-{number:5}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Names
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'default' => 'default',
        'costs' => 'costs',
        'reports' => 'reports',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'dashboard_kpis' => 60,        // 1 minute
        'product_costs' => 600,        // 10 minutes
        'inventory_summary' => 300,    // 5 minutes
        'reports' => 1800,             // 30 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */
    'per_page' => 25,
    'per_page_options' => [10, 25, 50, 100],

    /*
    |--------------------------------------------------------------------------
    | Units of Measure
    |--------------------------------------------------------------------------
    */
    'units' => [
        'weight' => ['g', 'kg', 'lb', 'oz'],
        'volume' => ['ml', 'l', 'fl_oz'],
        'length' => ['mm', 'cm', 'm'],
        'unit' => ['und', 'pza', 'par', 'doc', 'paq'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Export Formats
    |--------------------------------------------------------------------------
    */
    'export_formats' => ['pdf', 'xlsx', 'csv'],
];
