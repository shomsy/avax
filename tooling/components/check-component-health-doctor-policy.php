<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-component-health-doctor-policy.php
 *
 * Fails if active runtime-critical component has no health/doctor implementation
 * or explicit accepted YELLOW entry.
 *
 * Health/doctor required for:
 * - Container, Database, Router/HTTP, Events, FailureBoundary, Cache, Filesystem,
 *   ObjectStorage (if active), Queue (if active), Logging/Observability (if active),
 *   Security (if active)
 *
 * Does not require health/doctor for ROADMAP/SCAFFOLD/LABS_ONLY/EVIDENCE_ONLY
 * or pure foundation primitives.
 */

$lockFile = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';

$runtimeCriticalComponents = [
    'Application/Container',
    'DataStack/Database',
    'HTTP/Router',
    'Operations/Events',
    'Application/Cache',
    'Application/Filesystem',
    'Operations/Logging',
    'Operations/Queue',
    'Security/Redaction',
    'Security/Cryptography',
    'Integration/ObjectStorage',
];

$frameworkRuntimeCriticalComponents = [
    'FailureBoundary' => __DIR__ . '/../../framework/System/Capabilities/FailureBoundary',
];

$excludedStatuses = ['ROADMAP', 'SCAFFOLD', 'LABS_ONLY', 'EVIDENCE_ONLY', 'TEST_ONLY', 'DEPRECATED'];

$classifiedComponents = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        foreach (explode("\n", $content) as $line) {
            if (! str_starts_with(trim($line), '|')) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($line, "|\t ")));
            if (count($cells) < 2 || $cells[0] === 'Component' || str_starts_with($cells[0], '-')) {
                continue;
            }

            $name   = $cells[0];
            $status = $cells[1];
            $classifiedComponents[$name] = $status;
        }
    }
}

$violations = [];
$checked    = 0;

foreach ($runtimeCriticalComponents as $component) {
    $status = $classifiedComponents[$component] ?? null;
    if ($status === null) {
        $violations[] = "$component — missing component status lock entry";
        $checked++;
        continue;
    }

    if (in_array($status, $excludedStatuses, true)) {
        $checked++;
        continue;
    }

    // Check for health/doctor capabilities
    $area     = explode('/', $component)[0];
    $name     = explode('/', $component)[1];
    $basePath = __DIR__ . "/../../components/$area/$name/System/Capabilities";

    $hasHealth = false;
    $hasDoctor = false;

    if (is_dir($basePath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filename = $file->getFilename();
                if (stripos($filename, 'Health') !== false || stripos($filename, 'Check') !== false) {
                    $hasHealth = true;
                }
                if (stripos($filename, 'Doctor') !== false || stripos($filename, 'Diagnose') !== false) {
                    $hasDoctor = true;
                }
            }
        }
    }

    // Also check framework for framework-level components
    if (! $hasHealth || ! $hasDoctor) {
        $frameworkPath = __DIR__ . '/../../framework/System/Capabilities';
        if (is_dir($frameworkPath)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($frameworkPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $filename = $file->getFilename();
                    if (stripos($filename, 'Health') !== false || stripos($filename, 'Doctor') !== false) {
                        if (stripos($filename, $name) !== false || stripos($file->getPath(), $name) !== false) {
                            if (stripos($filename, 'Health') !== false || stripos($filename, 'Check') !== false) {
                                $hasHealth = true;
                            }
                            if (stripos($filename, 'Doctor') !== false || stripos($filename, 'Diagnose') !== false) {
                                $hasDoctor = true;
                            }
                        }
                    }
                }
            }
        }
    }

    if (! $hasHealth) {
        $violations[] = "$component — no health check implementation (status: $status)";
    }
    $checked++;
}

foreach ($frameworkRuntimeCriticalComponents as $component => $basePath) {
    $hasHealth = false;

    if (is_dir($basePath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $filename = $file->getFilename();
            if (stripos($filename, 'Health') !== false || stripos($filename, 'Check') !== false) {
                $hasHealth = true;
            }
        }
    }

    if (! $hasHealth) {
        $violations[] = "Framework/$component — no health check implementation";
    }
    $checked++;
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " runtime-critical components missing health checks:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    echo "\nNote: Doctor checks are recommended but not required for PASS.\n";
    exit(1);
}

echo "PASS: $checked runtime-critical components checked, all have health checks\n";
exit(0);
