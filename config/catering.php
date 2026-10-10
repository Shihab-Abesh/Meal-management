<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | Used everywhere money is displayed. All amounts are stored as decimals
    | in the database; this only controls how they are presented.
    |
    */

    'currency' => [
        'code' => env('APP_CURRENCY', 'BDT'),
        'symbol' => env('APP_CURRENCY_SYMBOL', '৳'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vendor Profile
    |--------------------------------------------------------------------------
    |
    | Suggested business types shown on the vendor profile form. The field
    | accepts any value, so this list only provides quick suggestions.
    |
    */

    'business_types' => [
        'Catering Service',
        'Corporate Meal Supplier',
        'Restaurant',
        'Cloud Kitchen',
        'Home Kitchen',
        'Other',
    ],

    'logo' => [
        'directory' => 'vendor',
        'max_kb' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Defaults
    |--------------------------------------------------------------------------
    |
    | Used until the vendor changes the value in the app (stored in the
    | settings table). Prices are per meal; cutoffs are local times.
    |
    */

    'defaults' => [
        'meals.lunch_price' => '120.00',
        'meals.dinner_price' => '120.00',
        'meals.lunch_cutoff' => '10:00',
        'meals.dinner_cutoff' => '16:00',
        // Days after the issue date a bill must be paid by.
        'billing.due_days' => 15,
        // Emails to client accounts (sent with the mailer in .env).
        'notifications.bill_issued' => true,
        'notifications.payment_received' => true,
        'notifications.order_delivered' => false,
    ],

    'menu_image' => [
        'directory' => 'menu',
        'max_kb' => 2048,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seed Accounts
    |--------------------------------------------------------------------------
    |
    | Credentials for the first vendor admin created by the database seeder.
    | Change them in .env before seeding a production database.
    |
    */

    'seed' => [
        'admin' => [
            'name' => env('SEED_ADMIN_NAME', 'Vendor Admin'),
            'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
            'phone' => env('SEED_ADMIN_PHONE'),
            'password' => env('SEED_ADMIN_PASSWORD', 'password'),
        ],
    ],

];
