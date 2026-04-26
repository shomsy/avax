<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Providers;

use Avax\Container\DI\ContainerInterface;

/**
 * Service provider contract for deterministic registration.
 */
interface ServiceProviderInterface
{
    /**
     * Creates one provider bound to one container facade.
     */
    public function __construct(ContainerInterface $app);

    /**
     * Returns provider dependencies that must be resolved first.
     *
     * @return list<class-string<ServiceProviderInterface>>
     */
    public function dependsOn() : array;

    /**
     * Registers provider-owned services.
     */
    public function register() : void;

    /**
     * Boots provider-owned side effects after registration.
     */
    public function boot() : void;
}
