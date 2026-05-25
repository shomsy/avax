<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Auth\System\PublicSurface\AuthInterface;


/**
 * AuthServiceProvider — registers Identity/Auth component dependencies.
 *
 * Registers all default infrastructure dependencies that AuthBuilder previously
 * created with `?? new` fallback patterns. This eliminates the fallback anti-pattern
 * by pre-registering default implementations in the composition root.
 */
final class AuthServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        (new Builders\RegisterAuthDefaults())->register($container);
    }

    public function boot(ContainerInterface $container) : void
    {
        // Bridge DI-registered Auth instance to deprecated static auth() helper
        if ($container->has(AuthInterface::class)) {
            Auth::setInstance($container->get(AuthInterface::class));
        }

        // Worker safety — reset static state for long-lived runtimes
        Auth::instance()->logout();
    }
}
