<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Providers\Contracts;

use Avax\Container\Container;

/**
 * Service provider contract for deterministic registration.
 *
 */
interface ServiceProviderInterface
{
    public function __construct(Container $app);

    public function register() : void;

    public function boot() : void;
}
