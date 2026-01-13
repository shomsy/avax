<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Avax\HTTP\Session\Shared\Contracts\SessionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use SensitiveParameter;

/**
 * PSR-15 Middleware responsible for managing the session lifecycle during an HTTP request.
 *
 * Handles:
 * - Session start
 * - FlashBag load/sweep
 * - PSR-15 response validation
 * - Optional native session write-close
 */
final readonly class SessionLifecycleMiddleware implements MiddlewareInterface
{
    public function __construct(
        #[SensitiveParameter] private SessionInterface $session
    ) {}

    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        // Ensure PHP session is started with the configured cookie policy.
        $this->session->start();

        $response = $handler->handle(request: $request);

        if (! $response instanceof ResponseInterface) {
            // This should not happen in a properly configured PSR-15 chain
            // But if it does, create a proper error response
            throw new RuntimeException(message: 'Middleware chain did not return a valid ResponseInterface.');
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $response;
    }
}
