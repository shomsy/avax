<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Core\Contracts;

/**
 * FeatureKernelInterface
 *
 * Contract for feature kernels.
 *
 * @package Avax\HTTP\Session\Core\Contracts
 */
interface FeatureKernelInterface
{
    /**
     * Get feature name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Register feature components.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Boot feature.
     *
     * @return void
     */
    public function boot(): void;
}
