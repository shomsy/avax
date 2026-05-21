<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime;
use Avax\Components\Identity\System\Configuration\Builders\IdentityRuntime as BuildIdentityRuntime;
use Avax\Components\Identity\System\Configuration\IdentityConfiguration;

/**
 * IdentityServiceProvider — registers identity aggregate component dependencies.
 */
final class IdentityServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        if (! $container->has(IdentityConfiguration::class)) {
            $container->singleton(IdentityConfiguration::class, static fn () : IdentityConfiguration => IdentityConfiguration::fromEnvironment());
        }

        $container->singleton(
            BuildIdentityRuntime::class,
            static function (ContainerInterface $c) : BuildIdentityRuntime {
                $configuration = $c->get(IdentityConfiguration::class);

                if (! $configuration instanceof IdentityConfiguration) {
                    throw new \RuntimeException('IdentityConfiguration binding must resolve to IdentityConfiguration.');
                }

                return BuildIdentityRuntime::defaults(configuration: $configuration);
            },
        );
        $container->singleton(
            IdentityRuntime::class,
            static function (ContainerInterface $c) : IdentityRuntime {
                $builder = $c->get(BuildIdentityRuntime::class);

                if (! $builder instanceof BuildIdentityRuntime) {
                    throw new \RuntimeException('Identity runtime builder binding must resolve to BuildIdentityRuntime.');
                }

                return $builder->runtime();
            },
        );
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
