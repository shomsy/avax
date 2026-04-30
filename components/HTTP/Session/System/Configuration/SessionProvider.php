<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit;
use Avax\Components\HTTP\Session\System\Capabilities\Events\SessionEventBus;
use Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\NativeSessionStore;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Psr\Log\LoggerInterface;

final class SessionProvider implements ComponentProviderInterface
{
    public function register(ComponentRegistry $registry): void
    {
        $registry->singleton(SessionStoreInterface::class, NativeSessionStore::class);
        $registry->singleton(SessionScope::class, static fn ($container) => new SessionScope($container->get(SessionStoreInterface::class)));
        $registry->singleton(SessionInterface::class, static fn ($container) => new Session(
            scope   : $container->get(SessionScope::class),
            metadata: $container->has(SessionMetadata::class) ? $container->get(SessionMetadata::class) : null,
            audit   : $container->has(SessionAudit::class) ? $container->get(SessionAudit::class) : null,
            events  : $container->has(SessionEventBus::class) ? $container->get(SessionEventBus::class) : null,
            logger  : $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null,
        ));
        $registry->alias(Session::class, SessionInterface::class);
    }
}
