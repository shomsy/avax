<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\NativeSessionStore;
use Avax\Components\HTTP\Session\System\Capabilities\Storage\SessionStoreInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\Session;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionInterface;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentProviderInterface;

final class SessionProvider implements ComponentProviderInterface
{
    public function register(ComponentRegistry $registry): void
    {
        $registry->singleton(SessionStoreInterface::class, NativeSessionStore::class);
        $registry->singleton(SessionScope::class, function ($container) {
            return new SessionScope($container->get(SessionStoreInterface::class));
        });
        $registry->singleton(SessionInterface::class, function ($container) {
            return new Session($container->get(SessionScope::class));
        });
        $registry->alias(Session::class, SessionInterface::class);
    }
}
