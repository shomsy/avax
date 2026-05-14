<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\NativeSessionStore;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Psr\Log\LoggerInterface;

/**
 * SessionServiceProvider — registers HTTP session component dependencies.
 */
final class SessionServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Session store — native PHP session default
        $container->singleton(SessionStoreInterface::class, static fn () : NativeSessionStore => new NativeSessionStore());

        // Session scope — requires store
        $container->singleton(SessionScope::class, static fn (ContainerInterface $c) : SessionScope => new SessionScope(
            store: $c->get(SessionStoreInterface::class),
        ));

        // Session — optional metadata, audit, event bus, logger resolved from container
        $container->singleton(SessionInterface::class, static fn (ContainerInterface $c) : Session => new Session(
            sessionScope   : $c->get(SessionScope::class),
            sessionMetadata: $c->has(\Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata::class)
                ? $c->get(\Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata::class)
                : null,
            sessionAudit   : $c->has(\Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit::class)
                ? $c->get(\Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit::class)
                : null,
            sessionEventBus: $c->has(\Avax\Components\HTTP\Session\System\Capabilities\SessionEvents\SessionEventBus::class)
                ? $c->get(\Avax\Components\HTTP\Session\System\Capabilities\SessionEvents\SessionEventBus::class)
                : null,
            logger         : $c->has(LoggerInterface::class)
                ? $c->get(LoggerInterface::class)
                : null,
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
