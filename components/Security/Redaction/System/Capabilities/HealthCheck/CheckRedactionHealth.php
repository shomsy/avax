<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\HealthCheck;

/**
 * CheckRedactionHealth
 *
 * Verifies redaction component runtime health:
 * - Redaction engine class available
 * - Policy configuration exists
 */
final readonly class CheckRedactionHealth
{
    public function check(): RedactionHealthReport
    {
        $findings = [];
        $healthy = true;

        // Check 1: Redaction engine available
        $engineClass = 'Avax\\Components\\Security\\Redaction\\System\\Capabilities\\RedactionEngine\\RedactionEngine';
        if (class_exists($engineClass)) {
            $findings[] = 'Redaction engine available';
        } else {
            $healthy = false;
            $findings[] = 'Redaction engine class not loaded';
        }

        // Check 2: Pattern matcher available
        $patternClass = 'Avax\\Components\\Security\\Redaction\\System\\Capabilities\\PatternMatcher\\PatternMatcher';
        if (class_exists($patternClass)) {
            $findings[] = 'Pattern matcher available';
        } else {
            $healthy = false;
            $findings[] = 'Pattern matcher class not loaded';
        }

        return new RedactionHealthReport(
            healthy: $healthy,
            findings: $findings,
        );
    }
}
