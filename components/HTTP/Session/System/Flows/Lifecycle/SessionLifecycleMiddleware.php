<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\Lifecycle;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\Request\System\PublicSurface\ServerRequest;
use Closure;

final readonly class SessionLifecycleMiddleware
{
    public function __construct(
        private Session $session,
    ) {}

    public function handle(ServerRequest $serverRequest, Closure $next) : mixed
    {
        $this->session->start();
        $this->session->ageFlash();

        $response = $next($serverRequest);

        // Write close is handled implicitly by the destructors/flush in the new arch,
        // but if we were using native sessions, we would call session_write_close() here.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $response;
    }
}
