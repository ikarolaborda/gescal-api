<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report File Storage
    |--------------------------------------------------------------------------
    |
    | Configure the filesystem disk used for storing generated report files.
    | This should match a disk configured in config/filesystems.php
    |
    */
    'storage_disk' => env('REPORT_STORAGE_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | File Retention Period
    |--------------------------------------------------------------------------
    |
    | Number of days to keep generated report files before automatic deletion.
    | Metadata persists indefinitely, but files are deleted after this period.
    |
    */
    'file_retention_days' => env('REPORT_FILE_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | File Path Structure
    |--------------------------------------------------------------------------
    |
    | Template for report file storage path. Available variables:
    | {year}, {month}, {report_id}, {extension}
    |
    */
    'file_path_template' => 'reports/{year}/{month}/{report_id}.{extension}',

    /*
    |--------------------------------------------------------------------------
    | Maximum Records Per Report
    |--------------------------------------------------------------------------
    |
    | Maximum number of records allowed in a single report to prevent
    | system overload and excessive memory usage.
    |
    */
    'max_records_per_report' => env('REPORT_MAX_RECORDS', 10000),

    /*
    |--------------------------------------------------------------------------
    | Async Processing Threshold
    |--------------------------------------------------------------------------
    |
    | Reports taking longer than this many seconds to generate will be
    | queued for asynchronous processing with email notification on completion.
    |
    */
    'async_threshold_seconds' => env('REPORT_ASYNC_THRESHOLD', 30),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue name and connection for report generation jobs.
    |
    */
    'queue' => [
        'connection' => env('REPORT_QUEUE_CONNECTION', 'redis'),
        'name' => env('REPORT_QUEUE_NAME', 'reports'),
        'max_workers' => env('REPORT_MAX_WORKERS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for report generation and schedule management.
    |
    */
    'rate_limits' => [
        'generation_per_hour' => env('REPORT_RATE_LIMIT_GENERATION', 60),
        'schedule_per_hour' => env('REPORT_RATE_LIMIT_SCHEDULE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for report completion email notifications.
    |
    */
    'email' => [
        'download_link_expiration_days' => env('REPORT_EMAIL_LINK_EXPIRATION_DAYS', 7),
        'from_address' => env('REPORT_EMAIL_FROM', env('MAIL_FROM_ADDRESS', 'reports@gescal.app')),
        'from_name' => env('REPORT_EMAIL_FROM_NAME', 'GESCAL Report System'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for PDF report generation using DomPDF.
    |
    */
    'pdf' => [
        'paper_size' => 'A4',
        'orientation_portrait_max_columns' => 7,
        'font_family' => 'DejaVu Sans',
        'font_size_body' => 10,
        'font_size_header' => 12,
        'margin_cm' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Excel Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Excel report generation using Laravel Excel.
    |
    */
    'excel' => [
        'freeze_header_row' => true,
        'auto_width_max_chars' => 50,
        'header_background_color' => 'F0F0F0',
    ],

    /*
    |--------------------------------------------------------------------------
    | CSV Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for CSV report generation (RFC 4180 compliant).
    |
    */
    'csv' => [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape_char' => '"',
        'include_bom' => true, // UTF-8 BOM for Excel compatibility
        'line_ending' => "\r\n", // CRLF for Windows compatibility
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for JSON report generation.
    |
    */
    'json' => [
        'pretty_print' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Entity Types
    |--------------------------------------------------------------------------
    |
    | Entity types that can be included in reports.
    |
    */
    'entity_types' => [
        'persons',
        'families',
        'cases',
        'benefits',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Export Formats
    |--------------------------------------------------------------------------
    |
    | Available report export formats and their MIME types.
    |
    */
    'formats' => [
        'pdf' => [
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
        ],
        'excel' => [
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'extension' => 'xlsx',
        ],
        'csv' => [
            'mime_type' => 'text/csv',
            'extension' => 'csv',
        ],
        'json' => [
            'mime_type' => 'application/vnd.api+json',
            'extension' => 'json',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Field Masking
    |--------------------------------------------------------------------------
    |
    | Configuration for PII field masking for coordinator role.
    | Administrators see unmasked values.
    |
    */
    'pii_masking' => [
        'enabled' => true,
        'mask_value' => '***',
        'person_fields' => [
            'email',
            'primary_phone',
            'secondary_phone',
        ],
        'address_fields' => [
            'street',
            'number',
            'complement',
            'neighborhood',
            'zip_code',
            'reference_point',
        ],
        'document_fields' => [
            'number',
            'issuing_body',
        ],
    ],
];
