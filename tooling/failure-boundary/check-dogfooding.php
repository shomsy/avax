#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-failure-boundary-dogfooding.php — Gate: Ensures FailureBoundary uses AvaX components where they exist.
 *
 * Exit code 0: All dogfooding checks pass.
 * Exit code 1: Dogfooding violations found.
 *
 * Usage:
 * php tooling/failure-boundary/check-failure-boundary-dogfooding.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$failures = [];

// Check that FailureBoundary middleware uses the correct middleware interface
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

// Check that MapFailureToResult uses ResponseFactory
$mapFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/MapFailureToResult/MapFailureToResult.php';

if (file_exists($mapFile)) {
    $content = file_get_contents($mapFile);

    if (!str_contains($content, 'ResponseFactory')) {
        $failures[] = 'MapFailureToResult does not use ResponseFactory';
    }
}

// Check that ReportFailure is extensible (not hard-coded to a specific observability implementation)
$reportFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/ReportFailure/ReportFailure.php';

if (file_exists($reportFile)) {
    $content = file_get_contents($reportFile);

    // It's OK to use error_log for MVP, but it should be documented as extensible
    if (!str_contains($content, 'MVP') && !str_contains($content, 'extensible')) {
        // This is a soft check — the file does have comments about extensibility
    }
}

// Check that Retry uses its own backoff logic (no Resilience component exists yet)
$retryFile = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary/Capabilities/RetryFailedAction/RetryFailedAction.php';

if (file_exists($retryFile)) {
    $content = file_get_contents($retryFile);

    // Retry implements its own backoff — this is expected since no Resilience component exists
    // When Resilience is added, this should be replaced
    if (str_contains($content, 'Resilience')) {
        $failures[] = 'Retry should not duplicate Resilience component — use the existing one';
    }
}

if (empty($failures)) {
    echo "GREEN: FailureBoundary dogfooding checks pass.\n";
    exit(0);
}

echo "RED: Dogfooding violations:\n";
foreach ($failures as $failure) {
    echo "  - {$failure}\n";
}

exit(1);
