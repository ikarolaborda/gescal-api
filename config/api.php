<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Versioning Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API versioning behavior including deprecation settings.
    |
    */

    /*
     * Current API version (primary/recommended version)
     */
    'current_version' => '1.0',

    /*
     * Is V1 deprecated?
     * Set to true when you want to start the deprecation process for V1.
     * This will add deprecation headers to all V1 responses.
     */
    'v1_deprecated' => env('API_V1_DEPRECATED', false),

    /*
     * V1 Deprecation Date
     * The date when V1 was marked as deprecated (RFC 7231 format)
     */
    'v1_deprecation_date' => env('API_V1_DEPRECATION_DATE', null),

    /*
     * V1 Sunset Date
     * The date when V1 will be discontinued (RFC 7231 format)
     * Should be at least 12 months after deprecation date
     */
    'v1_sunset_date' => env('API_V1_SUNSET_DATE', null),

    /*
     * Migration guide URL
     * URL to the documentation for migrating from V1 to V2
     */
    'migration_guide_url' => env('API_MIGRATION_GUIDE_URL', 'https://docs.example.com/api/migration-guide'),

];
