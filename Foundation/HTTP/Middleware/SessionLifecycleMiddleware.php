<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Session\Contracts\SessionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class SessionLifecycleMiddleware
 *
 * Middleware responsible for managing the session lifecycle during an HTTP request.
 * Handles:
 * - Session start
 * - FlashBag load/sweep
 * - PSR-15 response validation
 * - Optional native session write-close
 *
 * @final
 */
final readonly class SessionLifecycleMiddleware
{
    public function __construct(
        private SessionInterface $session
    ) {}

    public function handle(RequestInterface $request, callable $next) : ResponseInterface
    {
        // Ensure PHP session is started with the configured cookie policy.
        $this->session->start();

        $response = $next($request);

        if (! $response instanceof ResponseInterface) {
            error_log('Invalid response returned from middleware chain.');

            $response = response(
                status : 500,
                headers: [],
                body   : 'Middleware chain did not return a valid ResponseInterface.'
            );
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $response;
    }
}
