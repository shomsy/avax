<?php

// Infrastructure/Config/middleware.php
// NOTE: This is an example configuration file. Middleware classes referenced here
// are placeholders demonstrating the intended middleware pipeline structure.

declare(strict_types=1);

// TODO: Replace with actual AvaX middleware classes when available.
// These are illustrative examples of middleware pipeline configuration.

// Existing middleware (confirmed):
use Avax\Components\HTTP\Middleware\RateLimiterMiddleware;
use Avax\Components\HTTP\Middleware\RequestLoggerMiddleware;

return [
    'global' => [
        // TODO: ExceptionHandlerMiddleware - central exception handling
        // TODO: SecurityHeadersMiddleware - security headers
        // TODO: SessionLifecycleMiddleware - session management
        RequestLoggerMiddleware::class,    // Logs request details for tracking and debugging purposes
    ],
    'groups' => [
        'api' => [
            // TODO: CorsMiddleware - CORS configuration
            RateLimiterMiddleware::class,     // Enforces rate limiting to prevent abuse of the API
            // TODO: JsonResponseMiddleware - JSON response formatting
            // TODO: AuthenticationMiddleware - API authentication
        ],
        'web' => [
            // TODO: OfficeIpRestrictionMiddleware - IP-based access control
            // TODO: AuthenticationMiddleware - web authentication
        ],
    ],
];
