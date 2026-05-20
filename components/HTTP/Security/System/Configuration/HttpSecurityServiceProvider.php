<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Security\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Security\System\Capabilities\Csrf\CsrfTokens;
use Avax\Components\HTTP\Security\System\PublicSurface\Security;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Psr\Log\LoggerInterface;

/**
 * HttpSecurityServiceProvider — registers HTTP Security component dependencies.
 *
 * Registers CSRF token management and the security public surface.
 * Uses cross-component dependency on HTTP/Session for CSRF token storage.
 */
final class HttpSecurityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // CsrfTokens — requires Session, optional LoggerInterface
        $container->singleton(CsrfTokens::class, static fn (ContainerInterface $c) : CsrfTokens => new CsrfTokens(
            session: $c->get(Session::class),
            logger : $c->has(LoggerInterface::class) ? $c->get(LoggerInterface::class) : null,
        ));

        // Security (PublicSurface) — requires CsrfTokens
        $container->singleton(Security::class, static fn (ContainerInterface $c) : Security => new Security(
            csrfTokens: $c->get(CsrfTokens::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
