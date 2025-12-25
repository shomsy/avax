<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Contracts;

/**
 * FeatureInterface - Session Feature Lifecycle Contract
 *
 * Defines lifecycle hooks for session features.
 * Enables automatic initialization and cleanup.
 *
 * Features implement this interface to:
 * - Initialize on session start (boot)
 * - Cleanup on session end (terminate)
 * - Declare feature name for debugging
 *
 * @package Avax\HTTP\Session\Contracts
 */
interface FeatureInterface
{
    /**
     * Boot the feature.
     *
     * Called when session starts or feature is first accessed.
     * Use for initialization logic.
     *
     * @return void
     */
    public function boot() : void;

    /**
     * Terminate the feature.
     *
     * Called when session terminates or is destroyed.
     * Use for cleanup logic (flush logs, save state, etc).
     *
     * @return void
     */
    public function terminate() : void;

    /**
     * Get feature name.
     *
     * @return string Feature identifier.
     */
    public function getName() : string;

    /**
     * Check if feature is enabled.
     *
     * @return bool True if feature is active.
     */
    public function isEnabled() : bool;
}
