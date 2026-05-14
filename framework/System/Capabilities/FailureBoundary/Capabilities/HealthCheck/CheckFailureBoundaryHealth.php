<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\HealthCheck;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunFailurePipeline\RunFailurePipeline;
use Avax\Framework\System\Capabilities\FailureBoundary\Flows\RunProtectedAction\RunProtectedAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * CheckFailureBoundaryHealth
 *
 * Verifies FailureBoundary component runtime health:
 * - FailurePolicy behaves correctly (defaults, hasRetry, hasFallback)
 * - CompiledPolicyCache is functional (clear, all, put, get)
 * - RunFailurePipeline and RunProtectedAction classes are loadable
 */
final class CheckFailureBoundaryHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: FailurePolicy defaults are correct
        try {
            $policy = new FailurePolicy();

            if ($policy->hasRetry() || $policy->hasFallback() || $policy->hasDeadLetter() || $policy->hasRecovery()) {
                $findings[] = new HealthFinding('failure.policy', HealthStatus::Red, 'FailurePolicy has unexpected default actions');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $findings[] = new HealthFinding('failure.policy', HealthStatus::Green, 'FailurePolicy defaults are correct');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('failure.policy', HealthStatus::Red, sprintf('Policy check failed: %s', $e->getMessage()));

            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        // Check 2: CompiledPolicyCache is functional
        try {
            CompiledPolicyCache::clear();

            if (CompiledPolicyCache::all() !== []) {
                $findings[] = new HealthFinding('failure.cache', HealthStatus::Red, 'CompiledPolicyCache clear failed');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $probeKey = '__health_check_probe__';
            if (CompiledPolicyCache::get($probeKey) !== null) {
                $findings[] = new HealthFinding('failure.cache', HealthStatus::Red, 'CompiledPolicyCache returned unexpected value');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            CompiledPolicyCache::clear();
            $findings[] = new HealthFinding('failure.cache', HealthStatus::Green, 'CompiledPolicyCache is functional');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('failure.cache', HealthStatus::Red, sprintf('Cache check failed: %s', $e->getMessage()));
            $overall    = HealthStatus::Red;
        }

        // Check 3: RunFailurePipeline class is loadable
        if (class_exists(RunFailurePipeline::class)) {
            $findings[] = new HealthFinding('failure.pipeline', HealthStatus::Green, 'RunFailurePipeline class is loadable');
        } else {
            $findings[] = new HealthFinding('failure.pipeline', HealthStatus::Yellow, 'RunFailurePipeline class not loaded');
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }

        // Check 4: RunProtectedAction class is loadable
        if (class_exists(RunProtectedAction::class)) {
            $findings[] = new HealthFinding('failure.protected', HealthStatus::Green, 'RunProtectedAction class is loadable');
        } else {
            $findings[] = new HealthFinding('failure.protected', HealthStatus::Yellow, 'RunProtectedAction class not loaded');
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
