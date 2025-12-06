<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Events;

use Avax\HTTP\Session\Core\Contracts\FeatureKernelInterface;

/**
 * EventsFeatureKernel
 *
 * Kernel for Events feature registration.
 *
 * @package Avax\HTTP\Session\Features\Events
 */
final class EventsFeatureKernel implements FeatureKernelInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'events';
    }

    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        // Register event emitters and listeners
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        // Boot events feature
    }
}
