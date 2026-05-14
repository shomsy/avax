<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\WarmApplication;

/**
 * ResetWarmRequestState — Resets all request-scoped items after dispatch.
 *
 * For long-lived runtimes (ReactPHP, RoadRunner, Swoole, FrankenPHP),
 * each request must start with a clean slate.
 */
final class ResetWarmRequestState
{
    /**
     * @param list<callable(): void> $resetCallbacks
     */
    public function __construct(
        private array $resetCallbacks = [],
    ) {
    }

    public function reset(): void
    {
        foreach ($this->resetCallbacks as $callback) {
            $callback();
        }
    }

    public function addCallback(callable $callback): void
    {
        $this->resetCallbacks[] = $callback;
    }
}
