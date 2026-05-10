<?php

declare(strict_types=1);

/**
 * Check Component Adoption — Dogfooding Gate
 *
 * Verifies that components use canonical owners for shared capabilities:
 * - Redaction must use Security/Redaction (not local implementations)
 * - Queue must use Operations/Resilience for retry/deadletter
 * - Queue failed jobs must use explicit FailedJobsStore
 * - Queue static state must be reset-safe
 * - Storage must use Filesystem for file I/O
 * - Filesystem must not depend on Storage
 * - Logging/Observability file writers must use Filesystem
 * - Raw file gate must have 0 MIGRATE items
 * - NEEDS_DESIGN_DECISION items must be documented
 * - No duplicate capability implementations
 *
 * Exit 0 = adoption correct
 * Exit 1 = adoption violations found
 */

$rootDir       = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';

$violations = [];
$warnings   = [];
$documented = [];

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
if ($redactionComponents === [$canonicalRedaction]) {
    $documented[] = 'Redaction: canonical owner is Security/Redaction (verified)';
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

// ---- Check 2b: Queue failed jobs explicit store ----

$failedJobsStorePath = $componentsDir . '/Operations/Queue/System/Capabilities/Queue/FailedJobs/FailedJobsStore.php';
if (! is_file($failedJobsStorePath)) {
    $violations[] = 'QUEUE FAILED JOBS: FailedJobsStore interface not found — must have explicit store contract';
} else {
    $documented[] = 'Queue failed jobs: explicit FailedJobsStore interface exists (verified)';
}

$inMemoryStorePath = $componentsDir . '/Operations/Queue/System/Capabilities/Queue/FailedJobs/InMemoryFailedJobsStore.php';
if (! is_file($inMemoryStorePath)) {
    $violations[] = 'QUEUE FAILED JOBS: InMemoryFailedJobsStore not found — must have reset-safe default';
}

// ---- Check 2c: Queue static state reset-safety ----

$queuePath = $componentsDir . '/Operations/Queue/System/Capabilities/Queue/Queue.php';
if (is_file($queuePath)) {
    $queueContent = file_get_contents($queuePath);
    if (str_contains($queueContent, 'public static function reset()')) {
        $documented[] = 'Queue static state: reset() method exists (verified) — static facade is lightweight, canonical runtime uses instance-scoped MemoryQueue';
    } else {
        $violations[] = 'QUEUE STATIC STATE: Queue::reset() not found — static facade must be reset-safe';
    }
    if (str_contains($queueContent, 'useFailedJobsStore')) {
        $documented[] = 'Queue failed jobs: useFailedJobsStore() injection exists (verified)';
    }
}

// ---- Check 2d: Storage uses Filesystem ----

$storageDirs = [
    $componentsDir . '/Application/Storage/System/Capabilities/Disks/LocalDisk',
    $componentsDir . '/Application/Storage/System/Flows/CopyStoredObject',
];
foreach ($storageDirs as $storageDir) {
    if (! is_dir($storageDir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageDir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getRealPath());
        if (preg_match('/\bfile_put_contents\s*\(/', $content)
            || preg_match('/\bfile_get_contents\s*\(/', $content)
            || preg_match('/\bmkdir\s*\(/', $content)
            || preg_match('/\bunlink\s*\(/', $content)
        ) {
            // Check if Filesystem is also used
            if (! str_contains($content, 'use Avax\Components\Application\Filesystem')
                && ! str_contains($content, 'new Filesystem()')
            ) {
                $violations[] = sprintf(
                    'STORAGE FILESYSTEM: %s uses raw file operations without Filesystem',
                    str_replace($rootDir . '/', '', $file->getRealPath())
                );
            }
        }
    }
}
if (! array_filter($violations, static fn (string $v) => str_starts_with($v, 'STORAGE FILESYSTEM:'))) {
    $documented[] = 'Storage: uses Filesystem for file I/O (verified)';
}

// ---- Check 2e: Filesystem does not depend on Storage ----

$filesystemDir = $componentsDir . '/Application/Filesystem';
if (is_dir($filesystemDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filesystemDir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $content = file_get_contents($file->getRealPath());
        if (str_contains($content, 'Avax\Components\Application\Storage')) {
            $violations[] = sprintf(
                'FILESYSTEM DEPENDENCY: %s depends on Storage — Filesystem must not depend on Storage',
                str_replace($rootDir . '/', '', $file->getRealPath())
            );
        }
    }
    if (! array_filter($violations, static fn (string $v) => str_starts_with($v, 'FILESYSTEM DEPENDENCY:'))) {
        $documented[] = 'Filesystem: does not depend on Storage (verified)';
    }
}

// ---- Check 2f: Logging/Observability file writers use Filesystem ----

$loggingDirs = [
    $componentsDir . '/Operations/Logging',
    $componentsDir . '/Operations/Observability',
];
foreach ($loggingDirs as $loggingDir) {
    if (! is_dir($loggingDir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($loggingDir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $relativePath = str_replace($rootDir . '/', '', $file->getRealPath());
        // Skip FileLogWriter which has documented performance exception
        if (str_ends_with($relativePath, 'Observability/System/Capabilities/Logging/FileLogWriter.php')) {
            continue;
        }
        $content = file_get_contents($file->getRealPath());
        if (preg_match('/\bfile_put_contents\s*\(/', $content)
            || preg_match('/\bfile_get_contents\s*\(/', $content)
            || preg_match('/\bmkdir\s*\(/', $content)
            || preg_match('/\bunlink\s*\(/', $content)
        ) {
            $violations[] = sprintf(
                'LOGGING OBSERVABILITY FILESYSTEM: %s uses raw file operations — must use Filesystem',
                $relativePath
            );
        }
    }
}
if (! array_filter($violations, static fn (string $v) => str_starts_with($v, 'LOGGING OBSERVABILITY FILESYSTEM:'))) {
    $documented[] = 'Logging/Observability: file writers use Filesystem (verified)';
}

// ---- Check 2g: Raw file gate status ----

$rawGatePath = $rootDir . '/tooling/security/check-raw-file-operations.php';
if (is_file($rawGatePath)) {
    exec('php ' . escapeshellarg($rawGatePath) . ' 2>&1', $rawGateOutput, $rawGateExitCode);
    $rawGateText = implode("\n", $rawGateOutput);
    if (preg_match('/MIGRATE_TO_FILESYSTEM \(MUST FIX\): (\d+)/', $rawGateText, $matches)) {
        $migrateFs = (int) $matches[1];
        if ($migrateFs > 0) {
            $violations[] = "RAW FILE GATE: {$migrateFs} MIGRATE_TO_FILESYSTEM violations remain";
        } else {
            $documented[] = 'Raw file gate: MIGRATE_TO_FILESYSTEM = 0 (verified)';
        }
    }
    if (preg_match('/MIGRATE_TO_STORAGE \(MUST FIX\): (\d+)/', $rawGateText, $matches)) {
        $migrateStorage = (int) $matches[1];
        if ($migrateStorage > 0) {
            $violations[] = "RAW FILE GATE: {$migrateStorage} MIGRATE_TO_STORAGE violations remain";
        } else {
            $documented[] = 'Raw file gate: MIGRATE_TO_STORAGE = 0 (verified)';
        }
    }
    if (preg_match('/NEEDS DESIGN DECISION \(REVIEW\): (\d+)/', $rawGateText, $matches)) {
        $needsDesign = (int) $matches[1];
        if ($needsDesign > 0) {
            $warnings[] = "RAW FILE GATE: {$needsDesign} NEEDS_DESIGN_DECISION items — must be documented in final report";
        }
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

echo "COMPONENT ADOPTION GATE\n";
echo str_repeat('=', 60) . "\n\n";

if ($documented !== []) {
    echo "DOCUMENTED:\n";
    foreach ($documented as $item) {
        echo "  ✓ {$item}\n";
    }
    echo "\n";
}

if ($warnings !== []) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  ⚠ {$warning}\n";
    }
    echo "\n";
}

if ($violations !== []) {
    echo "VIOLATIONS:\n";
    foreach ($violations as $violation) {
        echo "  ✗ {$violation}\n";
    }
    echo "\n";
}

if ($emptyComponents !== []) {
    echo "EMPTY COMPONENT SHELLS:\n";
    foreach ($emptyComponents as $comp) {
        echo "  - {$comp} (0 capabilities, 0 flows, 0 public surface)\n";
    }
    echo "\n";
}

if ($violations === [] && $emptyComponents === []) {
    $totalDocumented = count($documented);
    $totalWarnings   = count($warnings);
    echo "Result: PASS — {$totalDocumented} checks verified";
    if ($totalWarnings > 0) {
        echo ", {$totalWarnings} warning(s) documented";
    }
    echo "\n";
    exit(0);
}

$totalIssues = count($violations) + count($emptyComponents);
echo "Result: FAIL — {$totalIssues} issue(s)\n";
exit(1);
