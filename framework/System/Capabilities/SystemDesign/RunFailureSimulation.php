<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\SystemDesign;

use Avax\Framework\System\Capabilities\SystemDesign\Foundation\FailureSimulationResult;

final class RunFailureSimulation
{
    /**
     * Simulate a failure scenario and return findings.
     *
     * @param array<string, mixed> $runtimeState
     */
    public function simulate(string $scenario, array $runtimeState = []): FailureSimulationResult
    {
        return match ($scenario) {
            'queue_failure' => $this->simulateQueueFailure($runtimeState),
            'database_failure' => $this->simulateDatabaseFailure($runtimeState),
            'message_relay_failure' => $this->simulateMessageRelayFailure($runtimeState),
            'memory_pressure' => $this->simulateMemoryPressure($runtimeState),
            default => new FailureSimulationResult(
                $scenario,
                'unknown',
                ["Unknown scenario: {$scenario}"],
                ['Define a simulation for this scenario.'],
            ),
        };
    }

    /** @param array<string, mixed> $state */
    private function simulateQueueFailure(array $state): FailureSimulationResult
    {
        $findings = ['If queue worker crashes, in-memory messages are lost.', 'Dead letter queue captures poison messages.'];
        $recommendations = ['Use persistent queue driver (database/Redis) for production.', 'Configure max_attempts and dead letter routing.'];

        $failedJobs = $state['failed_jobs'] ?? 0;
        if ($failedJobs > 0) {
            $findings[] = "{$failedJobs} failed jobs detected.";
        }

        return new FailureSimulationResult('queue_failure', 'simulated', $findings, $recommendations);
    }

    /** @param array<string, mixed> $state */
    private function simulateDatabaseFailure(array $state): FailureSimulationResult
    {
        $findings = ['Database failure blocks all persistence operations.', 'Connection pool should have health checks.'];
        $recommendations = ['Configure connection timeouts and retry policies.', 'Use circuit breaker for database calls.'];

        $poolSize = $state['pool_size'] ?? 0;
        if ($poolSize > 0) {
            $findings[] = "Connection pool size: {$poolSize}.";
        }

        return new FailureSimulationResult('database_failure', 'simulated', $findings, $recommendations);
    }

    /** @param array<string, mixed> $state */
    private function simulateMessageRelayFailure(array $state): FailureSimulationResult
    {
        $findings = ['Message relay failure causes outbox backlog.', 'Outbox pattern ensures eventual consistency when relay recovers.'];
        $recommendations = ['Monitor outbox pending count.', 'Configure alerting on relay failures.'];

        $pending = $state['outbox_pending'] ?? 0;
        if ($pending > 0) {
            $findings[] = "{$pending} messages pending in outbox.";
        }

        return new FailureSimulationResult('message_relay_failure', 'simulated', $findings, $recommendations);
    }

    /** @param array<string, mixed> $state */
    private function simulateMemoryPressure(array $state): FailureSimulationResult
    {
        $findings = ['Memory pressure in long-lived workers causes degraded performance.', 'MemoryGuard should trigger worker recycle on threshold breach.'];
        $recommendations = ['Configure memory guard soft and hard thresholds.', 'Monitor worker memory growth rate.'];

        $memoryMb = $state['memory_mb'] ?? 0;
        if ($memoryMb > 0) {
            $findings[] = "Current worker memory: {$memoryMb}MB.";
        }

        return new FailureSimulationResult('memory_pressure', 'simulated', $findings, $recommendations);
    }
}
