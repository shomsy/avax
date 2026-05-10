<?php

declare(strict_types=1);

/**
 * Check Raw File Operations — Dogfooding Gate
 *
 * Scans for raw file operations (file_put_contents, file_get_contents, fopen, etc.)
 * outside the Filesystem component owner.
 *
 * Per how-to-dogfooding.md: Storage uses Filesystem. No raw file operations outside owner.
 *
 * Categories:
 *   ALLOWED_OWNER        — Filesystem component internals (canonical owner)
 *   ALLOWED_TOOLING      — tooling/ scripts
 *   ALLOWED_TEST         — tests/ and fixtures
 *   ALLOWED_BOOTSTRAP    — explicitly documented early bootstrap paths
 *   MIGRATE_TO_FILESYSTEM — production runtime code doing local file I/O (MUST fix)
 *   MIGRATE_TO_STORAGE   — object/disk abstraction code that should use Storage (MUST fix)
 *   NEEDS_DESIGN_DECISION — unclear owner, must be documented
 *
 * Exit 0 = no MIGRATE violations (ALLOWED and NEEDS_DESIGN_DECISION may remain in report-only)
 * Exit 1 = MIGRATE_TO_FILESYSTEM or MIGRATE_TO_STORAGE violations found
 */

$rootDir       = dirname(__DIR__, 2);
$frameworkDir  = $rootDir . '/framework';
$componentsDir = $rootDir . '/components';

// Categories: ALLOWED_OWNER, ALLOWED_TOOLING, ALLOWED_TEST, ALLOWED_BOOTSTRAP,
//             MIGRATE_TO_FILESYSTEM, MIGRATE_TO_STORAGE, NEEDS_DESIGN_DECISION
$categories = [
    'ALLOWED_OWNER'         => [],
    'ALLOWED_TOOLING'       => [],
    'ALLOWED_TEST'          => [],
    'ALLOWED_BOOTSTRAP'     => [],
    'MIGRATE_TO_FILESYSTEM' => [],
    'MIGRATE_TO_STORAGE'    => [],
    'NEEDS_DESIGN_DECISION' => [],
];

$rawFunctions = [
    'file_put_contents',
    'file_get_contents',
    'fopen(',
    'fclose(',
    'fread(',
    'fwrite(',
    'unlink(',
    'mkdir(',
    'chmod(',
    'rmdir(',
    'copy(',
    'rename(',
];

// Allowed path prefixes and their reasons
$allowedPathRules = [
    // Filesystem component — canonical owner of file I/O
    ['path' => 'components/Application/Filesystem/', 'category' => 'ALLOWED_OWNER', 'reason' => 'Filesystem canonical owner internals'],
    ['path' => 'components/Operations/Filesystem/', 'category' => 'ALLOWED_OWNER', 'reason' => 'Filesystem adapters/drivers internals'],

    // Tooling scripts
    ['path' => 'tooling/', 'category' => 'ALLOWED_TOOLING', 'reason' => 'Developer tooling scripts'],

    // Tests and fixtures
    ['path' => 'tests/', 'category' => 'ALLOWED_TEST', 'reason' => 'Test files and fixtures'],

    // Container tooling scripts
    ['path' => '/tools/', 'category' => 'ALLOWED_TOOLING', 'reason' => 'Container tooling scripts'],

    // Bootstrap / dev server (documented exception)
    ['path' => 'framework/System/Capabilities/Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php', 'category' => 'ALLOWED_BOOTSTRAP', 'reason' => 'Built-in dev server bootstrap'],
    ['path' => 'framework/System/Capabilities/Runtime/PublicSurface/Server.php', 'category' => 'ALLOWED_BOOTSTRAP', 'reason' => 'Built-in dev server bootstrap facade'],

    // FileLogWriter fopen/fwrite — documented performance tradeoff for batch write with open file handle
    // Uses Filesystem for directory creation, keeps native handle for write performance
    // Classified as NEEDS_DESIGN_DECISION: file-stream boundary not yet in Filesystem API
    ['path' => 'components/Operations/Observability/System/Capabilities/Logging/FileLogWriter.php', 'category' => 'NEEDS_DESIGN_DECISION', 'reason' => 'Documented performance tradeoff: open file handle for batch write; needs Filesystem stream boundary'],

    // CompiledCacheDirectory is_file — type metadata check to distinguish file from directory
    // Filesystem API does not expose isFile()/isDirectory() type distinction
    ['path' => 'components/Application/Cache/System/Capabilities/CompiledCache/ManageCompiledCache/CompiledCacheDirectory.php', 'category' => 'NEEDS_DESIGN_DECISION', 'reason' => 'Type metadata check: Filesystem API lacks isFile()/isDirectory() distinction'],
];

// Explicit function-level exclusions (method names that happen to match)
$functionExclusions = [
    'function rename(',   // method definition, not filesystem call
    'function fopen(',
    'function fclose(',
    'function fread(',
    'function fwrite(',
    'function unlink(',
    'function mkdir(',
    'function chmod(',
    'function rmdir(',
    'function copy(',
    'function file_put_contents(',
    'function file_get_contents(',
    '->rename(',          // method call on object, not native function
    '->copy(',            // method call on object, not native function
    '$this->copy(',       // immutable value object copy method, not file copy
    'self::copy(',        // immutable value object copy method, not file copy
    'static::copy(',      // immutable value object copy method, not file copy
];

// Paths that use php:// streams (legitimate non-filesystem I/O)
$streamExclusions = [
    'php://input',
    'php://temp',
    'php://memory',
    'php://stdout',
    'php://stderr',
];

$directories = [$frameworkDir, $componentsDir];
$allFiles    = [];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $allFiles[] = $file->getRealPath();
    }
}

foreach ($allFiles as $path) {
    $relativePath = str_replace($rootDir . '/', '', $path);

    // Check explicit allowed path rules
    $matchedRule = null;
    foreach ($allowedPathRules as $rule) {
        if (str_contains($relativePath, $rule['path'])) {
            $matchedRule = $rule;
            break;
        }
    }

    $content = file_get_contents($path);
    $lines   = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);

        // Skip comments
        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
            continue;
        }

        // Skip function definitions
        $isFunctionDef = false;
        foreach ($functionExclusions as $exclusion) {
            if (str_contains($trimmed, $exclusion)) {
                $isFunctionDef = true;
                break;
            }
        }
        if ($isFunctionDef) {
            continue;
        }

        // Skip stream-based I/O
        $isStream = false;
        foreach ($streamExclusions as $stream) {
            if (str_contains($line, $stream)) {
                $isStream = true;
                break;
            }
        }
        if ($isStream) {
            continue;
        }

        foreach ($rawFunctions as $func) {
            if (str_contains($line, $func)) {
                $violation = [
                    'function' => trim($func, '('),
                    'file'     => $relativePath,
                    'line'     => $lineNum + 1,
                    'snippet'  => $trimmed,
                ];

                if ($matchedRule !== null) {
                    $violation['reason']                    = $matchedRule['reason'];
                    $categories[$matchedRule['category']][] = $violation;
                } else {
                    // Not in an allowed path — classify by component context
                    $category                = classifyViolation($relativePath, $line, $func);
                    $categories[$category][] = $violation;
                }

                break; // Only report once per line
            }
        }
    }
}

/**
 * Classify a violation that is not in an explicitly allowed path.
 */
function classifyViolation(string $relativePath, string $line, string $func) : string
{
    // PreCommit framework — tooling-like context
    if (str_contains($relativePath, 'System/Capabilities/PreCommit/')) {
        return 'ALLOWED_TOOLING';
    }

    // ObjectStorage local filesystem adapter — should use Filesystem but is storage adapter boundary
    if (str_contains($relativePath, 'Integration/ObjectStorage/') && str_contains($relativePath, 'LocalFilesystem')) {
        return 'MIGRATE_TO_STORAGE';
    }

    // Container compilation — should migrate but is special runtime context
    if (str_contains($relativePath, 'Application/Container/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Cache compilation — should migrate
    if (str_contains($relativePath, 'Application/Cache/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Observability file writers — must migrate (PART 3 scope)
    if (str_contains($relativePath, 'Operations/Observability/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Logging file writers — must migrate (PART 3 scope)
    if (str_contains($relativePath, 'Operations/Logging/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Session file storage — filesystem behavior, legitimate file-based session handler
    if (str_contains($relativePath, 'HTTP/Session/') && str_contains($relativePath, 'FileSessionStore')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Route cache — should migrate
    if (str_contains($relativePath, 'Capabilities/Routing/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Database migration/export — should migrate
    if (str_contains($relativePath, 'DataStack/Database/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // View/template compilation — should migrate
    if (str_contains($relativePath, 'Presentation/View/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Code generation — should migrate
    if (str_contains($relativePath, 'DeveloperTools/CodeGeneration/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Config file loader — legitimate file reading for config
    if (str_contains($relativePath, 'Application/Config/') && str_contains($func, 'file_get_contents')) {
        return 'ALLOWED_BOOTSTRAP';
    }

    // Console UI — uses /dev/tty for terminal I/O, not filesystem
    if (str_contains($relativePath, 'CLI/Console/') && (str_contains($line, '/dev/tty') || str_contains($func, 'fopen'))) {
        return 'ALLOWED_BOOTSTRAP';
    }

    // CSV formatter — uses php://output or temp streams
    if (str_contains($relativePath, 'ContentNegotiation/') && str_contains($func, 'fclose')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Config commands — should migrate
    if (str_contains($relativePath, 'Configuration/') && str_contains($relativePath, 'framework/')) {
        return 'MIGRATE_TO_FILESYSTEM';
    }

    // Security/cryptography — needs design decision
    if (str_contains($relativePath, 'Security/Cryptography/') || str_contains($relativePath, 'Security/DataProtection/')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Privacy data exporter — uses streams, not filesystem
    if (str_contains($relativePath, 'Security/Privacy/') && str_contains($func, 'fclose')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // SystemDesign YAML parser — legitimate file reading
    if (str_contains($relativePath, 'SystemDesign/') && str_contains($func, 'file_get_contents')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Storage local disk — legitimate storage adapter
    if (str_contains($relativePath, 'Application/Storage/') && str_contains($relativePath, 'LocalDisk')) {
        return 'MIGRATE_TO_STORAGE';
    }

    // Storage copy flow
    if (str_contains($relativePath, 'Application/Storage/') && str_contains($func, 'copy')) {
        return 'MIGRATE_TO_STORAGE';
    }

    // Memory disk — uses copy for internal behavior
    if (str_contains($relativePath, 'Application/Storage/') && str_contains($relativePath, 'MemoryDisk')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Token codec — HMAC key ring file reading
    if (str_contains($relativePath, 'Identity/Tokens/') && str_contains($func, 'file_get_contents')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Webhook dispatcher — uses file_get_contents for HTTP calls (not filesystem)
    if (str_contains($relativePath, 'ApiBlueprint/') && str_contains($func, 'file_get_contents')) {
        return 'ALLOWED_BOOTSTRAP';
    }

    // UploadedFile — uses fopen for file uploads
    if (str_contains($relativePath, 'HTTP/Request/') && str_contains($relativePath, 'UploadedFile')) {
        return 'NEEDS_DESIGN_DECISION';
    }

    // Default: needs design decision
    return 'NEEDS_DESIGN_DECISION';
}

// ---- Output ----

$totalViolations   = 0;
$migrateViolations = 0;

echo "RAW FILE OPERATIONS GOVERNANCE GATE\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($categories as $category => $violations) {
    $count           = count($violations);
    $totalViolations += $count;

    if ($category === 'MIGRATE_TO_FILESYSTEM' || $category === 'MIGRATE_TO_STORAGE') {
        $migrateViolations += $count;
    }

    $marker = match ($category) {
        'ALLOWED_OWNER'         => 'ALLOWED',
        'ALLOWED_TOOLING'       => 'ALLOWED',
        'ALLOWED_TEST'          => 'ALLOWED',
        'ALLOWED_BOOTSTRAP'     => 'ALLOWED',
        'MIGRATE_TO_FILESYSTEM' => 'MUST FIX',
        'MIGRATE_TO_STORAGE'    => 'MUST FIX',
        'NEEDS_DESIGN_DECISION' => 'REVIEW',
    };

    echo "  {$category} ({$marker}): {$count}\n";

    // Print details for MUST FIX and REVIEW categories
    if ($marker !== 'ALLOWED' && $violations !== []) {
        foreach ($violations as $v) {
            $reason    = $v['reason'] ?? '';
            $reasonStr = $reason !== '' ? " [{$reason}]" : '';
            echo "    - {$v['function']} {$v['file']}:{$v['line']}{$reasonStr}\n";
        }
        echo "\n";
    }
}

echo str_repeat('=', 60) . "\n";
echo "Total: {$totalViolations} | MUST FIX: {$migrateViolations}\n\n";

// Allowed summary
$allowedTotal  = count($categories['ALLOWED_OWNER']) + count($categories['ALLOWED_TOOLING'])
    + count($categories['ALLOWED_TEST']) + count($categories['ALLOWED_BOOTSTRAP']);
$needsDecision = count($categories['NEEDS_DESIGN_DECISION']);

echo "Summary:\n";
echo "  ALLOWED: {$allowedTotal}\n";
echo "  MUST FIX (MIGRATE_TO_FILESYSTEM/STORAGE): {$migrateViolations}\n";
echo "  NEEDS DESIGN DECISION: {$needsDecision}\n\n";

if ($migrateViolations > 0) {
    echo "Result: FAIL — production runtime raw file operations must migrate to Filesystem\n";
    exit(1);
}

if ($needsDecision > 0) {
    echo "Result: WARN — {$needsDecision} violations need design decisions (report-only)\n";
    exit(0);
}

echo "Result: PASS — no production runtime raw file operations outside Filesystem owner\n";
exit(0);
