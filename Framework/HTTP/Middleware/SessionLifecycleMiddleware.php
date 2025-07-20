<?php

declare(strict_types=1);

namespace Gemini\HTTP\Middleware;

use Gemini\HTTP\Session\Contracts\SessionInterface;
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
        $bags = $this->session->getRegistry();

        $this->session->start();
        $bags->get('flash')->load();

        $response = $next($request);

        if (! $response instanceof ResponseInterface) {
            error_log('Invalid response returned from middleware chain.');

            $response = response(
                status : 500,
                headers: [],
                body   : 'Middleware chain did not return a valid ResponseInterface.'
            );
        }

        $bags->get('flash')->sweep();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $response;
    }
}
