<?php

// Infrastructure/Config/middleware.php

declare(strict_types=1);

// Middleware for handling Cross-Site Request Forgery (CSRF) protection.
// This middleware intercepts HTTP requests and ensures that a valid CSRF token is present.
// It is designed to prevent malicious activities such as CSRF attacks by verifying that the
// CSRF token submitted with the request matches the token stored in the user's session.
// If the token is missing or invalid, the request is rejected to protect the application.
use Avax\Auth\Interface\HTTP\Middleware\AuthenticationMiddleware;
use Avax\HTTP\Middleware\CorsMiddleware;
use Avax\HTTP\Middleware\ExceptionHandlerMiddleware;
use Avax\HTTP\Middleware\JsonResponseMiddleware;
use Avax\HTTP\Middleware\RateLimiterMiddleware;
use Avax\HTTP\Middleware\RequestLoggerMiddleware;
use Avax\HTTP\Middleware\SecurityHeadersMiddleware;
use Avax\HTTP\Middleware\SessionLifecycleMiddleware;
use Presentation\HTTP\Middleware\OfficeIpRestrictionMiddleware;

return [
    'global' => [
        ExceptionHandlerMiddleware::class, // Ensures all exceptions are handled centrally for consistency
        SecurityHeadersMiddleware::class,  // Adds key security headers to protect against common web vulnerabilities
        SessionLifecycleMiddleware::class,    // Manages user session lifecycle on web routes
        RequestLoggerMiddleware::class,    // Logs request details for tracking and debugging purposes
    ],
    'groups' => [
        'api' => [
            CorsMiddleware::class,            // Configures CORS to allow cross-origin requests for API endpoints
            RateLimiterMiddleware::class,     // Enforces rate limiting to prevent abuse of the API
            JsonResponseMiddleware::class,    // Ensures API responses are consistently formatted as JSON
            AuthenticationMiddleware::class,  // Verifies authentication for API routes and ensures secure access
        ],
        'web' => [
            OfficeIpRestrictionMiddleware::class,
            // Restricts web access to authorized office IP addresses
            //            AuthenticationMiddleware::class,      // Ensures that users are authenticated before accessing web routes
            //            VerifyCsrfToken::class,
            // Provides global protection against CSRF attacks
        ],
    ],
];
