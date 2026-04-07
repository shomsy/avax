<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Providers;

use Avax\Container\ContainerInterface;

/**
 * Service provider contract for deterministic registration.
 *
 */
interface ServiceProviderInterface
{
    public function __construct(ContainerInterface $app);

    public function register() : void;

    public function boot() : void;
}
