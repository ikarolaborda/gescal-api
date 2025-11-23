<?php

namespace App\Services\JsonApi;

use Illuminate\Validation\ValidationException;

class ErrorFormatterService
{
    /**
     * Convert Laravel validation errors to JSON:API format.
     *
     * @return array<string, mixed>
     */
    public static function fromValidationException(ValidationException $exception): array
    {
        $errors = [];

        foreach ($exception->errors() as $field => $messages) {
            foreach ($messages as $message) {
                $errors[] = [
                    'status' => '422',
                    'title' => 'Validation Error',
                    'detail' => $message,
                    'source' => [
                        'pointer' => '/data/attributes/' . str_replace('.', '/', $field),
                    ],
                ];
            }
        }

        return ['errors' => $errors];
    }

    /**
     * Convert a generic exception to JSON:API format.
     *
     * @param  \Throwable  $exception
     * @return array<string, mixed>
     */
    public static function fromException($exception, int $statusCode = 500): array
    {
        $error = [
            'status' => (string) $statusCode,
            'title' => class_basename($exception),
            'detail' => $exception->getMessage(),
        ];

        // Include trace in debug mode
        if (config('app.debug')) {
            $error['meta'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(5)->toArray(),
            ];
        }

        return ['errors' => [$error]];
    }

    /**
     * Create a generic error response.
     *
     * @return array<string, mixed>
     */
    public static function error(string $title, string $detail, int $status = 400, ?array $meta = null): array
    {
        $error = [
            'status' => (string) $status,
            'title' => $title,
            'detail' => $detail,
        ];

        if ($meta !== null) {
            $error['meta'] = $meta;
        }

        return ['errors' => [$error]];
    }

    /**
     * Create a not found error response.
     *
     * @return array<string, mixed>
     */
    public static function notFound(string $resource = 'Resource'): array
    {
        return self::error(
            'Not Found',
            "{$resource} not found",
            404
        );
    }

    /**
     * Create an unauthorized error response.
     *
     * @return array<string, mixed>
     */
    public static function unauthorized(string $detail = 'Authentication required'): array
    {
        return self::error(
            'Unauthorized',
            $detail,
            401
        );
    }

    /**
     * Create a forbidden error response.
     *
     * @return array<string, mixed>
     */
    public static function forbidden(string $detail = 'You do not have permission to access this resource'): array
    {
        return self::error(
            'Forbidden',
            $detail,
            403
        );
    }

    /**
     * Create a conflict error response (for optimistic locking).
     *
     * @return array<string, mixed>
     */
    public static function conflict(string $detail, ?array $currentState = null): array
    {
        $meta = null;
        if ($currentState !== null) {
            $meta = ['current_state' => $currentState];
        }

        return self::error(
            'Conflict',
            $detail,
            409,
            $meta
        );
    }
}
