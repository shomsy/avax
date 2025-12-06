<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Crypto;

use Avax\HTTP\Session\Core\Contracts\FeatureKernelInterface;

/**
 * CryptoFeatureKernel
 *
 * Kernel for Crypto feature registration.
 *
 * @package Avax\HTTP\Session\Features\Crypto
 */
final class CryptoFeatureKernel implements FeatureKernelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'crypto';
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        // Register crypto adapters and actions
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        // Boot crypto feature
    }
}
