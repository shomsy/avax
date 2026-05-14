<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\HTTP\Session\System\Capabilities\Audit\SessionAudit;
use Avax\Components\HTTP\Session\System\Capabilities\Metadata\SessionMetadata;
use Avax\Components\HTTP\Session\System\Capabilities\SessionEvents\SessionEventBus;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\NativeSessionStore;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Psr\Log\LoggerInterface;

final class SessionProvider implements ComponentProviderInterface
{
    public static function name() : string
    {
        return 'session';
    }

    public function boot(RuntimeInterface $runtime) : void
    {
        $this->register(componentRegistry: $runtime->components());
    }

    public function register(ComponentRegistry $componentRegistry) : void
    {
        $componentRegistry->single(SessionStoreInterface::class, static fn () : NativeSessionStore => new NativeSessionStore());

        $componentRegistry->single(SessionScope::class, static fn (ComponentRegistry $registry) : SessionScope => new SessionScope(store: $registry->get(SessionStoreInterface::class)));

        $componentRegistry->single(SessionInterface::class, static fn (ComponentRegistry $registry) : Session => new Session(
            sessionScope   : $registry->get(SessionScope::class),
            sessionMetadata: $registry->has(SessionMetadata::class) ? $registry->get(SessionMetadata::class) : null,
            sessionAudit   : $registry->has(SessionAudit::class) ? $registry->get(SessionAudit::class) : null,
            sessionEventBus: $registry->has(SessionEventBus::class) ? $registry->get(SessionEventBus::class) : null,
            logger         : $registry->has(LoggerInterface::class) ? $registry->get(LoggerInterface::class) : null,
        ));

        // Use name 'session' as per name() method
        $componentRegistry->single('session', static fn (ComponentRegistry $registry) : SessionInterface => $registry->get(SessionInterface::class));
    }
}
