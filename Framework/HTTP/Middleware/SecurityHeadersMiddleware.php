<?php
declare(strict_types=1);

namespace Gemini\HTTP\Middleware;

use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;
use Closure;

/**
 * The SecurityHeadersMiddleware class adds essential security-related headers
 * to HTTP responses to mitigate common web vulnerabilities.
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and add security headers to the response.
     *
     * @param Request $request The incoming HTTP request.
     * @param Closure $next A Closure that passes the request to the next middleware.
     *
     * @return ResponseInterface The HTTP response with security headers added.
     */
    public function handle(Request $request, Closure $next): ResponseInterface
    {
        // Pass the request to the next middleware and get the response.
        $response = $next($request);

        // Adding security headers to the response to prevent certain types of attacks:
        // - X-Content-Type-Options: Prevents the browser from MIME-sniffing the content type.
        // - X-Frame-Options: Prevents the page from being displayed in a frame or iframe.
        // - X-XSS-Protection: Enables Cross-Site Scripting (XSS) filter built into most browsers.
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('X-XSS-Protection', '1; mode=block');
    }
}