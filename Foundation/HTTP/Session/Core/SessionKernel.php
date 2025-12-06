<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core;

use Avax\HTTP\Session\Core\Contracts\FeatureKernelInterface;

/**
 * SessionKernel
 *
 * Main kernel for registering and booting all session features.
 *
 * @package Avax\HTTP\Session\Core
 */
final class SessionKernel
{
    /**
     * Registered feature kernels.
     *
     * @var array<FeatureKernelInterface>
     */
    private array $kernels = [];

    /**
     * Register a feature kernel.
     *
     * @param FeatureKernelInterface $kernel The feature kernel.
     *
     * @return self
     */
    public function register(FeatureKernelInterface $kernel): self
    {
        $this->kernels[$kernel->getName()] = $kernel;
        $kernel->register();

        return $this;
    }

    /**
     * Boot all registered kernels.
     *
     * @return void
     */
    public function boot(): void
    {
        foreach ($this->kernels as $kernel) {
            $kernel->boot();
        }
    }

    /**
     * Get registered kernel by name.
     *
     * @param string $name The kernel name.
     *
     * @return FeatureKernelInterface|null
     */
    public function get(string $name): FeatureKernelInterface|null
    {
        return $this->kernels[$name] ?? null;
    }

    /**
     * Get all registered kernels.
     *
     * @return array<FeatureKernelInterface>
     */
    public function all(): array
    {
        return $this->kernels;
    }
}
