<?php

declare(strict_types=1);

/**
 * Check Component Adoption — Dogfooding Gate
 *
 * Verifies that components use canonical owners for shared capabilities:
 * - Redaction must use Security/Redaction (not local implementations)
 * - Queue must use Operations/Resilience for retry/deadletter
 * - No duplicate capability implementations
 *
 * Exit 0 = adoption correct
 * Exit 1 = adoption violations found
 */

$rootDir       = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';

$violations = [];

// ---- Check 1: Duplicate Redaction capabilities ----

$redactionComponents = [];
$iterator            = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getRealPath();
    // Check if file is inside a Capabilities/Redaction/ directory
    if (preg_match('#components/([^/]+/[^/]+)/System/Capabilities/Redaction/#', $path, $matches)) {
        $component = $matches[1];
        if (! in_array($component, $redactionComponents, true)) {
            $redactionComponents[] = $component;
        }
    }
}

$canonicalRedaction = 'Security/Redaction';
foreach ($redactionComponents as $component) {
    if ($component !== $canonicalRedaction) {
        $violations[] = sprintf(
            'DUPLICATE REDACTION: %s has local Redaction capability — should use Security/Redaction',
            $component
        );
    }
}

// ---- Check 2: Duplicate DeadLetter capabilities ----

$deadLetterComponents = [];
$iterator             = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getRealPath();
    if (preg_match('#components/([^/]+/[^/]+)/System/Capabilities/DeadLetter/#', $path, $matches)) {
        $component = $matches[1];
        if (! in_array($component, $deadLetterComponents, true)) {
            $deadLetterComponents[] = $component;
        }
    }
}

$canonicalDeadLetter = 'Operations/Resilience';
foreach ($deadLetterComponents as $component) {
    if ($component !== $canonicalDeadLetter) {
        $violations[] = sprintf(
            'DUPLICATE DEADLETTER: %s has local DeadLetter capability — should use Operations/Resilience',
            $component
        );
    }
}

// ---- Check 3: Forbidden capability names (Adapters, Drivers, Services, etc.) ----

$forbiddenNames = ['Adapters', 'Drivers', 'Services', 'Helpers', 'Utils', 'Managers', 'Handlers', 'Processors'];
$forbiddenDirs  = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir));
foreach ($iterator as $file) {
    if (! $file->isDir()) {
        continue;
    }
    $dirName = $file->getFilename();
    if (in_array($dirName, $forbiddenNames, true)) {
        $path = str_replace($componentsDir . '/', '', $file->getPathname());
        // Only flag if inside Capabilities folder
        if (str_contains($path, '/Capabilities/')) {
            $forbiddenDirs[] = $path;
        }
    }
}

foreach ($forbiddenDirs as $dir) {
    $violations[] = sprintf(
        'FORBIDDEN NAME: %s — concept words must not be capability folder names',
        $dir
    );
}

// ---- Check 4: Empty component shells ----

$emptyComponents = [];
$componentDirs   = glob($componentsDir . '/*/*', GLOB_ONLYDIR);
foreach ($componentDirs as $compDir) {
    $systemDir = $compDir . '/System';
    if (! is_dir($systemDir)) {
        continue;
    }

    $caps     = glob($systemDir . '/Capabilities/*', GLOB_ONLYDIR);
    $flows    = glob($systemDir . '/Flows/*', GLOB_ONLYDIR);
    $pubFiles = glob($systemDir . '/PublicSurface/*.php');

    $capCount  = $caps === false ? 0 : count($caps);
    $flowCount = $flows === false ? 0 : count($flows);
    $pubCount  = $pubFiles === false ? 0 : count($pubFiles);

    if ($capCount === 0 && $flowCount === 0 && $pubCount === 0) {
        $relativePath      = str_replace($componentsDir . '/', '', $compDir);
        $emptyComponents[] = $relativePath;
    }
}

// ---- Output ----

if ($violations === [] && $emptyComponents === []) {
    echo "Component adoption check passed.\n";
    exit(0);
}

if ($violations !== []) {
    echo "COMPONENT ADOPTION VIOLATIONS:\n";
    foreach ($violations as $violation) {
        echo "  - $violation\n";
    }
    echo "\n";
}

if ($emptyComponents !== []) {
    echo "EMPTY COMPONENT SHELLS:\n";
    foreach ($emptyComponents as $comp) {
        echo "  - $comp (0 capabilities, 0 flows, 0 public surface)\n";
    }
    echo "\n";
}

$totalIssues = count($violations) + count($emptyComponents);
echo "Result: FAIL — $totalIssues issue(s)\n";
exit(1);
