<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Providers\Runtime;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capability\Providers\Contracts\ServiceProviderInterface;

/**
 * Base service provider compatible with deterministic boot flow.
 *
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $app) {}

    public function register() : void
    {
        // optional
    }

    public function boot() : void
    {
        // optional
    }
}
