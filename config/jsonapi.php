<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JSON:API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for JSON:API compliance and behavior.
    |
    */

    'pagination' => [
        'default_size' => env('JSONAPI_DEFAULT_PAGE_SIZE', 25),
        'max_size' => env('JSONAPI_MAX_PAGE_SIZE', 100),
        'page_parameter' => 'page',
        'size_parameter' => 'size',
    ],

    'includes' => [
        'max_depth' => env('JSONAPI_MAX_INCLUDE_DEPTH', 2),
        'allow_nested' => true,
    ],

    'filtering' => [
        'strategy' => 'exact', // exact, partial, scope
    ],

    'sorting' => [
        'default_direction' => 'asc',
        'max_fields' => 5,
    ],

    'sparse_fieldsets' => [
        'enabled' => true,
    ],

    'meta' => [
        'include_total' => true,
        'include_page_info' => true,
    ],

    'headers' => [
        'accept' => 'application/vnd.api+json',
        'content_type' => 'application/vnd.api+json',
    ],

    'errors' => [
        'include_trace' => env('APP_DEBUG', false),
        'include_meta' => true,
    ],

];
