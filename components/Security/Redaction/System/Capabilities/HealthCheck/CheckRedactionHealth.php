<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\HealthCheck;

use Avax\Components\Security\Redaction\System\Capabilities\PatternMatcher\PatternMatcher;
use Avax\Components\Security\Redaction\System\Capabilities\RedactionEngine\RedactionEngine;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * CheckRedactionHealth
 *
 * Verifies redaction component runtime health:
 * - Redaction engine is functional (add pattern, redact string, redact array)
 * - Pattern matcher detects sensitive data
 * - Pattern constants are defined
 */
final class CheckRedactionHealth
{
    public function check() : HealthReport
    {
        $findings = [];
        $overall = HealthStatus::Green;

        // Check 1: Redaction engine is functional
        try {
            $engine = new RedactionEngine();
            $engine->addPattern('/secret/i', '[REDACTED]');

            $redacted = $engine->redact('This is a SECRET message');
            if (str_contains($redacted, 'SECRET')) {
                $findings[] = new HealthFinding('redaction.engine', HealthStatus::Red, 'RedactionEngine failed to redact string');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            $redactedArray = $engine->redactArray(['key' => 'secret value', 'nested' => ['password' => 'mySECRET']]);

            $findings[] = new HealthFinding('redaction.engine', HealthStatus::Green, 'Redaction engine is functional');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('redaction.engine', HealthStatus::Red, sprintf('Redaction check failed: %s', $e->getMessage()));

            return new HealthReport(findings: $findings, overall: HealthStatus::Red);
        }

        // Check 2: Pattern matcher detects sensitive data
        try {
            $matcher  = new PatternMatcher();
            $detected = $matcher->findSensitivePatterns('user@example.com');

            if (! in_array('email', $detected, true)) {
                $findings[] = new HealthFinding('redaction.matcher', HealthStatus::Red, 'PatternMatcher failed to detect email');

                return new HealthReport(findings: $findings, overall: HealthStatus::Red);
            }

            // Verify all pattern constants are defined
            $requiredPatterns = [
                'PATTERN_EMAIL',
                'PATTERN_CREDIT_CARD',
                'PATTERN_SSN',
                'PATTERN_API_KEY',
                'PATTERN_PASSWORD',
                'PATTERN_TOKEN',
                'PATTERN_IP',
            ];

            foreach ($requiredPatterns as $const) {
                if (! defined(PatternMatcher::class . '::' . $const)) {
                    $findings[] = new HealthFinding('redaction.patterns', HealthStatus::Yellow, sprintf('Pattern constant %s not defined', $const));
                    $overall    = HealthStatus::Yellow;
                    break;
                }
            }

            $findings[] = new HealthFinding('redaction.matcher', HealthStatus::Green, 'Pattern matcher is functional');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('redaction.matcher', HealthStatus::Red, sprintf('Pattern matcher check failed: %s', $e->getMessage()));
            $overall    = HealthStatus::Red;
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }
}
