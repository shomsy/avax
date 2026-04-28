<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Session\SessionInterface;
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
    private SessionInterface $session;

    public function __construct(
        #[SensitiveParameter] SessionInterface $session
    )
    {
        $this->session = $session;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        if (method_exists(object_or_class: $this->session, method: 'start')) {
            $this->session->start();
        }

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
