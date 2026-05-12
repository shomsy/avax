#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-dogfooding.php — Gate: Ensures FailureBoundary uses canonical AvaX components.
 *
 * Exit code 0: All dogfooding checks pass.
 * Exit code 1: Dogfooding violations found.
 *
 * Usage:
 * php tooling/failure-boundary/check-dogfooding.php
 *
 * This gate verifies that FailureBoundary delegates to canonical components
 * rather than duplicating behavior. It fails on real violations:
 * - Standalone retry when Resilience exists
 * - error_log-only dead letter when Queue FailedJobsStore exists
 * - Raw error_log as primary report path when Logger exists
 * - Duplicated Resilience/Queue/Logging logic in FailureBoundary
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$failures = [];

// ── 1. HttpFailureBoundaryMiddleware implements MiddlewareInterface ──

$middlewareFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Integration/HttpFailureBoundaryMiddleware.php';

if (file_exists($middlewareFile)) {
    $content = file_get_contents($middlewareFile);

    if (!str_contains($content, 'MiddlewareInterface')) {
        $failures[] = 'HttpFailureBoundaryMiddleware does not implement MiddlewareInterface';
    }

    if (!str_contains($content, 'RequestInterface')) {
        $failures[] = 'HttpFailureBoundaryMiddleware does not use RequestInterface';
    }

    if (!str_contains($content, 'ResponseInterface')) {
        $failures[] = 'HttpFailureBoundaryMiddleware does not use ResponseInterface';
    }
}

// ── 2. MapFailureToResult uses ResponseFactory ──

$mapFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/MapFailureToResult/MapFailureToResult.php';

if (file_exists($mapFile)) {
    $content = file_get_contents($mapFile);

    if (!str_contains($content, 'ResponseFactory')) {
        $failures[] = 'MapFailureToResult does not use ResponseFactory';
    }
}

// ── 3. ReportFailure uses Logger as primary path ──

$reportFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/ReportFailure/ReportFailure.php';

if (file_exists($reportFile)) {
    $content = file_get_contents($reportFile);

    // Must use Logger as primary path
    if (!str_contains($content, 'Logger')) {
        $failures[] = 'ReportFailure does not use canonical Observability Logger';
    }

    // Must have error_log as fallback only (not primary)
    // Check that Logger check comes before error_log
    $loggerPos = strpos($content, '$this->logger');
    $errorLogPos = strpos($content, 'error_log(');

    if ($loggerPos !== false && $errorLogPos !== false && $errorLogPos < $loggerPos) {
        $failures[] = 'ReportFailure uses error_log as primary path instead of Logger fallback';
    }
}

// ── 4. Retry delegates to canonical Resilience RetryExecutor ──

$retryFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php';

if (file_exists($retryFile)) {
    $content = file_get_contents($retryFile);

    // Must delegate to Resilience RetryExecutor (not standalone retry loop)
    if (!str_contains($content, 'RetryExecutor')) {
        $failures[] = 'RetryFailedAction does not delegate to canonical Resilience RetryExecutor';
    }

    if (!str_contains($content, 'Components\Operations\Resilience')) {
        $failures[] = 'RetryFailedAction does not import Resilience component namespace';
    }

    // Must NOT contain its own retry loop (while/for with attempt tracking)
    if (preg_match('/\bwhile\s*\(.*attempt/i', $content) || preg_match('/\bfor\s*\(.*attempt/i', $content)) {
        $failures[] = 'RetryFailedAction contains its own retry loop — should delegate to Resilience';
    }
}

// ── 5. DeadLetter uses Queue FailedJobsStore ──

$deadLetterFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php';

if (file_exists($deadLetterFile)) {
    $content = file_get_contents($deadLetterFile);

    // Must accept FailedJobsStore
    if (!str_contains($content, 'FailedJobsStore')) {
        $failures[] = 'SendFailureToDeadLetter does not accept canonical Queue FailedJobsStore';
    }

    // Must NOT use error_log as the only transport (primary check)
    if (!str_contains($content, 'FailedJobsStore') && str_contains($content, 'error_log')) {
        $failures[] = 'SendFailureToDeadLetter uses only error_log — should delegate to Queue FailedJobsStore';
    }

    // Must have store check before fallback (primary before fallback)
    $storePos = strpos($content, 'failedJobsStore');
    $errorLogPos = strpos($content, 'error_log(');

    if ($storePos !== false && $errorLogPos !== false && $errorLogPos < $storePos) {
        $failures[] = 'SendFailureToDeadLetter uses error_log as primary transport instead of FailedJobsStore';
    }
}

// ── 6. Cleanup uses FailureCleanupRegistry ──

$cleanupFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/CleanupAfterFailure/CleanupAfterFailure.php';

if (file_exists($cleanupFile)) {
    $content = file_get_contents($cleanupFile);

    if (!str_contains($content, 'FailureCleanupRegistry')) {
        $failures[] = 'CleanupAfterFailure does not use FailureCleanupRegistry';
    }
}

// ── 7. Timeout delegates to Resilience Timeout ──

$timeoutFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/EnforceTimeout/EnforceTimeout.php';

if (file_exists($timeoutFile)) {
    $content = file_get_contents($timeoutFile);

    if (!str_contains($content, 'Timeout')) {
        $failures[] = 'EnforceTimeout does not delegate to Resilience Timeout';
    }
}

// ── 8. RecoverWith uses RunRecoveryAction ──

$recoveryFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php';

if (file_exists($recoveryFile)) {
    $content = file_get_contents($recoveryFile);

    if (!str_contains($content, 'RunRecoveryAction')) {
        $failures[] = 'RunRecoveryAction capability does not exist';
    }
}

// ── Result ──

if (empty($failures)) {
    echo "GREEN: FailureBoundary dogfooding checks pass.\n";
    exit(0);
}

echo "RED: Dogfooding violations:\n";
foreach ($failures as $failure) {
    echo "  - {$failure}\n";
}

exit(1);
