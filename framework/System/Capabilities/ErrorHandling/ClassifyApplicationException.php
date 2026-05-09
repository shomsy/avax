<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ErrorHandling;

use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestAuthorizationFailed;
use Avax\Components\HTTP\SecureRequest\System\Foundation\Failure\SecureRequestValidationFailed;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\MethodNotAllowedException;
use Avax\Components\HTTP\Router\System\Foundation\Exceptions\RouteNotFoundException;
use Throwable;

/**
 * ClassifyApplicationException — Classifies exceptions into user-safe categories.
 *
 * Categories:
 * - validation: user input errors (422)
 * - authorization: security/permission errors (403)
 * - not_found: route not found (404)
 * - method_not_allowed: wrong HTTP method (405)
 * - system: internal errors (500)
 */
final readonly class ClassifyApplicationException
{
    /**
     * @return array{category: string, safeMessage: string, statusCode: int}
     */
    public function classify(Throwable $e): array
    {
        // Validation errors
        if ($e instanceof SecureRequestValidationFailed) {
            return [
                'category' => 'validation',
                'safeMessage' => 'Validation failed',
                'statusCode' => 422,
            ];
        }

        // Authorization errors
        if ($e instanceof SecureRequestAuthorizationFailed) {
            return [
                'category' => 'authorization',
                'safeMessage' => 'Unauthorized',
                'statusCode' => 403,
            ];
        }

        // Route not found
        if ($e instanceof RouteNotFoundException) {
            return [
                'category' => 'not_found',
                'safeMessage' => 'Not found',
                'statusCode' => 404,
            ];
        }

        // Method not allowed
        if ($e instanceof MethodNotAllowedException) {
            return [
                'category' => 'method_not_allowed',
                'safeMessage' => 'Method not allowed',
                'statusCode' => 405,
            ];
        }

        // System error (default)
        return [
            'category' => 'system',
            'safeMessage' => 'Internal server error',
            'statusCode' => 500,
        ];
    }
}
