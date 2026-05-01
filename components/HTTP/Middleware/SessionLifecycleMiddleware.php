<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Components\HTTP\Session\NullSession;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;

/**
 * Manages session lifecycle for HTTP requests.
 * Starts session at request beginning and saves it at end.
 */
final readonly class SessionLifecycleMiddleware implements MiddlewareInterface
{
    public function __construct(
        private SessionInterface|NullSession $session,
    ) {}

    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        if ($this->session instanceof SessionInterface) {
            $this->session->start();
        }

        $response = $next($request);

        if ($this->session instanceof SessionInterface) {
            $this->session->ageFlash();
            $this->session->save();
        }

        return $response;
    }
}
