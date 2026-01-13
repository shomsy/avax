<?php

declare(strict_types=1);

namespace Avax\Container\Providers;

use Avax\Container\Container;
use Avax\Container\Features\Core\Contracts\ServiceProviderInterface;

/**
 * Base service provider compatible with deterministic boot flow.
 *
 * @see docs/Providers/ServiceProvider.md#quick-summary
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
