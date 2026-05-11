<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\Runtime\MemoryGuard\RecordMemorySnapshot;
use Throwable;

/**
 * HandleWarmRequest — Orchestrates the complete warm request lifecycle.
 *
 * Lifecycle order:
 * 1. Request enters runtime
 * 2. Request scope opens (handled by caller via FlushScopedInstances)
 * 3. Controller/middleware executes (provided as callable)
 * 4. Response is produced
 * 5. Response is returned
 * 6. Reset lifecycle runs (flush scoped instances, reset registry)
 * 7. Request scope closes
 * 8. Memory snapshot is recorded
 * 9. Leak detection runs
 * 10. No request state remains
 *
 * Reset runs after both successful and failed requests.
 */
final class HandleWarmRequest
{
    private FlushScopedInstances $flusher;

    private ResetWarmRequestState $resetter;

    private DetectLeakedState $leakDetector;

    private RecordMemorySnapshot|null $memoryRecorder = null;

    private WarmStateContract|null $contract = null;

    private bool $resetRan = false;

    private StateResetReport|null $lastResetReport = null;

    public function __construct()
    {
        $this->flusher = new FlushScopedInstances();
        $this->resetter = new ResetWarmRequestState();
        $this->leakDetector = new DetectLeakedState();
    }

    public function setFlusher(FlushScopedInstances $flusher): self
    {
        $this->flusher = $flusher;

        return $this;
    }

    public function setResetter(ResetWarmRequestState $resetter): self
    {
        $this->resetter = $resetter;

        return $this;
    }

    public function setLeakDetector(DetectLeakedState $detector): self
    {
        $this->leakDetector = $detector;

        return $this;
    }

    public function setMemoryRecorder(RecordMemorySnapshot $recorder): self
    {
        $this->memoryRecorder = $recorder;

        return $this;
    }

    public function setContract(WarmStateContract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function flusher(): FlushScopedInstances
    {
        return $this->flusher;
    }

    public function resetter(): ResetWarmRequestState
    {
        return $this->resetter;
    }

    public function leakDetector(): DetectLeakedState
    {
        return $this->leakDetector;
    }

    public function lastResetReport() : StateResetReport|null
    {
        return $this->lastResetReport;
    }

    public function resetRan(): bool
    {
        return $this->resetRan;
    }

    /**
     * Handle a warm request through the complete lifecycle.
     *
     * @param callable(): RuntimeResponse $handler
     */
    public function handle(callable $handler): RuntimeResponse
    {
        $response = null;
        $thrown = null;

        try {
            $response = $handler();
        } catch (Throwable $thrown) {
            // Captured so reset still runs
        }

        // Always run reset lifecycle regardless of success or failure
        $this->runResetLifecycle();

        // Re-throw if handler failed
        if ($thrown !== null) {
            throw $thrown;
        }

        return $response;
    }

    /**
     * Run the complete reset lifecycle in deterministic order.
     */
    private function runResetLifecycle(): void
    {
        // 1. Flush scoped instances and run state reset registry
        $this->lastResetReport = $this->flusher->flush();

        // 2. Run registered reset callbacks
        $this->resetter->reset();

        // 3. Mark reset as executed
        $this->resetRan = true;

        // 4. Record memory snapshot if recorder available
        if ($this->memoryRecorder !== null) {
            $this->memoryRecorder->record();
        }
    }
}
