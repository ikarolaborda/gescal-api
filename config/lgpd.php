<?php

return [

    /*
    |--------------------------------------------------------------------------
    | LGPD (Lei Geral de Proteção de Dados) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Brazilian data protection law compliance.
    |
    */

    'retention_periods' => [
        /*
        |--------------------------------------------------------------------------
        | Soft Delete Retention Periods (in years)
        |--------------------------------------------------------------------------
        |
        | Configure how long soft-deleted records are retained before permanent
        | deletion. Set to null for indefinite retention.
        |
        */

        'person' => env('LGPD_RETENTION_person', 7),
        'families' => env('LGPD_RETENTION_FAMILIES', 7),
        'cases' => env('LGPD_RETENTION_CASES', 10),
        'benefits' => env('LGPD_RETENTION_BENEFITS', 7),
        'documents' => env('LGPD_RETENTION_DOCUMENTS', 7),
        'addresses' => env('LGPD_RETENTION_ADDRESSES', 7),
        'occurrences' => env('LGPD_RETENTION_OCCURRENCES', 10),
        'housing_units' => env('LGPD_RETENTION_HOUSING_UNITS', 7),
        'case_social_reports' => env('LGPD_RETENTION_REPORTS', 10),
    ],

    'pii_fields' => [
        /*
        |--------------------------------------------------------------------------
        | Personally Identifiable Information (PII) Fields
        |--------------------------------------------------------------------------
        |
        | Define which fields contain PII and their sensitivity level.
        | Levels: 'sensitive' (encrypted at rest), 'regular' (masked only)
        |
        */

        'sensitive' => [
            // Encrypted at rest in database
            'documents.number',
            'addresses.street',
            'addresses.number',
            'addresses.complement',
        ],

        'regular' => [
            // Masked in logs for non-admin users
            'person.full_name',
            'person.primary_phone',
            'person.secondary_phone',
            'person.email',
            'families.family_income_value',
        ],
    ],

    'masking' => [
        /*
        |--------------------------------------------------------------------------
        | PII Masking Rules
        |--------------------------------------------------------------------------
        |
        | Configure how PII is masked in logs and for non-admin users.
        |
        */

        'phone' => [
            'strategy' => 'middle', // Show first 4 and last 4 chars
            'char' => '*',
        ],

        'email' => [
            'strategy' => 'username', // Mask username part before @
            'char' => '*',
        ],

        'name' => [
            'strategy' => 'first_and_initial', // "João Silva" -> "João S."
        ],

        'document' => [
            'strategy' => 'last_four', // Show only last 4 chars
            'char' => '*',
        ],
    ],

    'audit' => [
        /*
        |--------------------------------------------------------------------------
        | PII Access Audit Trail
        |--------------------------------------------------------------------------
        |
        | Configure audit logging for PII access.
        |
        */

        'enabled' => env('LGPD_AUDIT_ENABLED', true),
        'log_channel' => env('LGPD_AUDIT_CHANNEL', 'daily'),
        'retention_days' => env('LGPD_AUDIT_RETENTION_DAYS', 3650), // 10 years
    ],

    'data_subject_rights' => [
        /*
        |--------------------------------------------------------------------------
        | Data Subject Rights
        |--------------------------------------------------------------------------
        |
        | Configure LGPD data subject rights endpoints and behavior.
        |
        */

        'export_format' => 'json', // json, csv
        'export_includes_relations' => true,
        'deletion_requires_approval' => true, // Admin must approve deletion requests
    ],

];
