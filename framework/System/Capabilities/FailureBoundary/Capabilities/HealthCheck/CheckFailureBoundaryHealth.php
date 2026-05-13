<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\HealthCheck;

/**
 * CheckFailureBoundaryHealth
 *
 * Verifies FailureBoundary component runtime health:
 * - Compiled failure policies metadata available
 * - Failure pipeline class available
 * - Decision resolver available
 */
final readonly class CheckFailureBoundaryHealth
{
    public function check(): FailureBoundaryHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Compiled metadata class available
        $compiledClass = 'Avax\\Framework\\System\\Capabilities\\FailureBoundary\\Capabilities\\CompileFailurePolicies\\CompiledFailurePolicyMetadata';
        if (class_exists($compiledClass)) {
            $findings[] = 'Compiled failure policy metadata class available';
        } else {
            $healthy = false;
            $findings[] = 'Compiled failure policy metadata class not loaded';
        }

        // Check 2: Failure pipeline available
        $pipelineClass = 'Avax\\Framework\\System\\Capabilities\\FailureBoundary\\Flows\\RunFailurePipeline\\RunFailurePipeline';
        if (class_exists($pipelineClass)) {
            $findings[] = 'Failure pipeline available';
        } else {
            $healthy = false;
            $findings[] = 'Failure pipeline class not loaded';
        }

        // Check 3: RunProtectedAction available
        $protectedClass = 'Avax\\Framework\\System\\Capabilities\\FailureBoundary\\Flows\\RunProtectedAction\\RunProtectedAction';
        if (class_exists($protectedClass)) {
            $findings[] = 'Protected action runner available';
        } else {
            $healthy = false;
            $findings[] = 'Protected action runner class not loaded';
        }

        return new FailureBoundaryHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
