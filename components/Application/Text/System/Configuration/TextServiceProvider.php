<?php

declare(strict_types=1);

namespace Avax\Components\Application\Text\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Text\System\Configuration\TextConfiguration;

/**
 * TextServiceProvider — registers text component dependencies.
 */
final class TextServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Text configuration — default text processing settings
        $container->singleton(TextConfiguration::class, static fn () : TextConfiguration => new TextConfiguration());
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed — Text uses value objects and static helpers
    }
}
