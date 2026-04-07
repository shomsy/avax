<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Providers\Runtime;

use Avax\Container\Container;
use Avax\Container\Capabilities\Providers\Contracts\ServiceProviderInterface;

/**
 * Base service provider compatible with deterministic boot flow.
 *
 */
abstract class ServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected Container $app) {}

    public function register() : void
    {
        // optional
    }

    public function boot() : void
    {
        // optional
    }
}
