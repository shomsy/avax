<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;

/**
 * DetectLeakedState — Detects if state leaked between requests in a warm worker.
 *
 * Supports both simple before/after hash comparison and structured leak detection
 * against the MustResetState contract.
 *
 * Returns UNKNOWN when a required subsystem is unavailable instead of fake GREEN.
 */
final class DetectLeakedState
{
    private string|null $snapshotBefore = null;
    private string|null $snapshotAfter  = null;

    /**
     * @var array<string, mixed>
     */
    private array $trackedState = [];

    private RequestScope|null $scope = null;

    private bool $scopeAvailable = false;

    /**
     * Capture the state identifier before request handling.
     */
    public function captureBefore(string $identifier): void
    {
        $this->snapshotBefore = $identifier;
    }

    /**
     * Capture the state identifier after request handling.
     */
    public function captureAfter(string $identifier): void
    {
        $this->snapshotAfter = $identifier;
    }

    /**
     * Track a specific piece of state that should be reset.
     */
    public function trackState(MustResetState $state, mixed $value): void
    {
        $this->trackedState[$state->value] = $value;
    }

    /**
     * Attach a request scope for structured leak detection.
     */
    public function setRequestScope(RequestScope $scope): void
    {
        $this->scope = $scope;
        $this->scopeAvailable = true;
    }

    /**
     * Check if a simple hash-based leak occurred.
     */
    public function hasLeak(): bool
    {
        return $this->snapshotBefore !== $this->snapshotAfter && $this->snapshotBefore !== null;
    }

    /**
     * Detect all currently tracked state leaks.
     *
     * @return list<RuntimeStateLeak>
     */
    public function detectLeaks(): array
    {
        $leaks = [];

        foreach ($this->trackedState as $stateKey => $value) {
            if ($value !== null) {
                $mustReset = $this->findMustResetStateByKey($stateKey);
                if ($mustReset !== null) {
                    $leaks[] = new RuntimeStateLeak(
                        leakedState: $mustReset,
                        detail: "value remains after request",
                    );
                }
            }
        }

        // Check request scope for residual data
        if ($this->scopeAvailable && $this->scope !== null && $this->scope->isOpen()) {
            $scopeData = $this->scope->all();
            if ($scopeData !== []) {
                $leaks[] = new RuntimeStateLeak(
                    leakedState: MustResetState::RequestScopedCache,
                    detail: 'request scope still contains data after request',
                );
            }
        }

        return $leaks;
    }

    /**
     * Check if the runtime is clean (no leaks detected).
     *
     * Returns 'green' when clean, 'finding' when leaks detected,
     * or 'unknown' when required subsystems are unavailable.
     */
    public function checkStatus(): string
    {
        // If scope tracking is requested but no scope attached, report UNKNOWN
        if (! $this->scopeAvailable && $this->scope === null) {
            // No scope tracking requested is valid — check simple leak only
            if ($this->hasLeak()) {
                return 'finding';
            }

            return 'green';
        }

        $leaks = $this->detectLeaks();
        if ($leaks !== []) {
            return 'finding';
        }

        if ($this->hasLeak()) {
            return 'finding';
        }

        return 'green';
    }

    /**
     * Clear all tracked state and snapshots.
     */
    public function clear(): void
    {
        $this->snapshotBefore = null;
        $this->snapshotAfter = null;
        $this->trackedState = [];
    }

    /**
     * Clear the leak status after it has been handled.
     */
    public function clearStatus(): void
    {
        $this->clear();
    }

    private function findMustResetStateByKey(string $key) : MustResetState|null
    {
        foreach (MustResetState::cases() as $state) {
            if ($state->value === $key) {
                return $state;
            }
        }

        return null;
    }
}
