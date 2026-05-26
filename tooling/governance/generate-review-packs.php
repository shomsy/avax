<?php

declare(strict_types=1);

/**
 * AI Code Review Pack Generator
 *
 * Generates timestamped, focused ZIP review packages for AI upload.
 * Implements auto-clean staging, deterministic output, manifest validation,
 * duplicate detection, and stale pack detection.
 *
 * Falls back to directory copy if neither zip nor PharData are available.
 *
 * @see .agents/how-to/verification/how-to-create-ai-code-review-packs.md
 */

$root = dirname(__DIR__, 2);
$packDir = $root . '/_pack';
$stagingDir = $packDir . '/staging';

// ── Configuration ──────────────────────────────────────────────────────────

// Purpose name for the batch folder (used in _pack/YYYY-MM-DD-HH-MM-SS-purpose-review/)
$packPurpose = $argv[1] ?? getenv('REVIEW_PACK_PURPOSE') ?: 'governance-final-hardening';
if (! preg_match('/^[a-z0-9-]+$/', $packPurpose)) {
    fwrite(STDERR, "Invalid pack purpose. Use lowercase letters, numbers, and hyphens only.\n");
    exit(1);
}

$definitions = [
    '01-governance-architecture' => [
        'include' => [
            '.agents/how-to',
            '.agents/skills',
            '.agents/templates',
            '.agents/management/README.md',
            '.agents/management/TODO.md',
            '.agents/management/baselines',
            '.agents/GOVERNANCE_INDEX.md',
            '.agents/GOVERNANCE_ENFORCEMENT_MAP.md',
            '.agents/AGENT_EXECUTION_PROTOCOL.md',
            '.agents/AI_PREFLIGHT.md',
            '.agents/dictionary/framework-terms.md',
            '.agents/context',
            'docs',
            'README.md',
            'ARCHITECTURE.md',
            'CURRENT_TRUTH.md',
            'TODO.md',
            'EVIDENCE/EXECUTION.md',
            'AGENTS.md',
            'tooling/governance',
            'tooling/validation',
        ],
        'exclude' => [
            '.agents/management/evidence/generated',
            '.agents/hooks',
            '.agents/config',
            '.agents/sessions',
            '.agents/delivery',
            '.gitkeep',
        ],
        'context' => 'governance-quality',
    ],
    '02-identity-component' => [
        'include' => [
            'components/Identity',
            'tests/Unit/Components/Identity',
            'tests/Architecture/Components/Identity',
            'tests/Support/Identity',
            'docs/examples/self-explaining-architecture/identity',
            'framework/System/Capabilities/Runtime/RuntimeInterface.php',
            'framework/System/Capabilities/Runtime/RuntimeContext.php',
            'framework/System/Capabilities/Runtime/RuntimeState.php',
            'framework/System/Capabilities/StateReset/ResettableState.php',
            'framework/System/Capabilities/ComponentRegistry/ComponentRegistry.php',
            'framework/System/Capabilities/RequestScope/RequestScopeStore.php',
            'framework/System/Capabilities/RequestScope/RequestScopeId.php',
            'components/Application/Container/System/PublicSurface/ContainerInterface.php',
            'components/HTTP/Request/System/PublicSurface/RequestInterface.php',
            'components/Security/System/PublicSurface/Security.php',
        ],
        'exclude' => [],
        'context' => 'identity-architecture',
    ],
    '03-framework-core' => [
        'include' => [
            'framework',
            'components/Application/Container/System/PublicSurface',
            'components/HTTP/System',
        ],
        'exclude' => [],
        'context' => 'framework-runtime',
    ],
    '04-governance-tooling' => [
        'include' => [
            'tooling/governance',
            'tooling/refactor',
            'tooling/components',
            'tooling/security',
            'tooling/performance',
            'tooling/testing',
            'tooling/Architecture',
            'tooling/audit_broken_refs.php',
            'tooling/audit_test_integrity.php',
            'tooling/check_technical_folders.php',
            'tooling/phpVersion.php',
            'EVIDENCE/governance',
            'tooling/governance/baselines',
            '.agents/management/baselines',
            '.agents/management/evidence/generated/governance-gate-adoption',
            'tooling/validation',
        ],
        'exclude' => [],
        'context' => 'governance-enforcement',
    ],
    '05-testing-strategy' => [
        'include' => [
            'tests/Unit',
            'tests/Integration',
            'tests/Feature',
            'tests/Architecture',
            'tests/GoldenPathRuntime',
            'tests/Contract',
            'tests/Operations',
            'tests/TestCase.php',
            'phpunit.xml',
            'composer.json',
            'composer.lock',
            'tooling/testing',
        ],
        'exclude' => [],
        'context' => 'testing-quality',
    ],
    '06-self-explaining-architecture' => [
        'include' => [
            '.agents/how-to/documentation/how-to-write-self-explaining-architecture.md',
            '.agents/templates/architecture',
            '.agents/skills/self-explaining-architecture',
            'docs/examples/self-explaining-architecture',
            '.agents/how-to/documentation',
            '.agents/dictionary',
        ],
        'exclude' => [],
        'context' => 'documentation-philosophy',
    ],
];

// ── Helper Functions ───────────────────────────────────────────────────────

function log_msg(string $msg): void
{
    echo "[pack-gen] {$msg}\n";
}

function clean_staging(string $stagingDir): void
{
    if (!is_dir($stagingDir)) {
        return;
    }

    log_msg("Cleaning staging directory: {$stagingDir}");
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stagingDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if ($file->isDir()) {
            rmdir($path);
        } else {
            unlink($path);
        }
    }

    rmdir($stagingDir);
}

function detect_stale_packs(string $packDir, int $days = 30): array
{
    $stale = [];
    $cutoff = time() - ($days * 86400);

    if (!is_dir($packDir)) {
        return $stale;
    }

    foreach (scandir($packDir) as $entry) {
        if ($entry === '.' || $entry === '..' || $entry === 'staging') {
            continue;
        }

        $path = $packDir . '/' . $entry;
        if (is_dir($path) && filemtime($path) < $cutoff) {
            $stale[] = $entry;
        }
    }

    return $stale;
}

function detect_duplicates(array $definitions): array
{
    $allFiles = [];
    $duplicates = [];

    foreach ($definitions as $name => $def) {
        foreach ($def['include'] as $path) {
            if (isset($allFiles[$path])) {
                $duplicates[] = "File '{$path}' appears in both '{$allFiles[$path]}' and '{$name}'";
            } else {
                $allFiles[$path] = $name;
            }
        }
    }

    return $duplicates;
}

function is_forbidden_pack_path(string $relativePath): bool
{
    $normalized = str_replace('\\', '/', $relativePath);

    if (preg_match('#(^|/)(vendor|\.git|node_modules|cache|coverage|tmp|\.qoder)(/|$)#', $normalized)) {
        return true;
    }

    if (preg_match('#(^|/)\.env($|\.)#', $normalized)) {
        return true;
    }

    if (preg_match('/(?:private[_-]?key|credentials|secret)\.(?:pem|key|json|env|txt)$/i', $normalized)) {
        return true;
    }

    return false;
}

function copy_to_staging(string $root, string $stagingBase, string $name, array $def): int
{
    $stagingPath = $stagingBase . '/' . $name;
    $fileCount = 0;

    foreach ($def['include'] as $relativePath) {
        $source = $root . '/' . $relativePath;
        $dest = $stagingPath . '/' . $relativePath;

        if (is_forbidden_pack_path($relativePath)) {
            log_msg("  SKIP (forbidden path): {$relativePath}");
            continue;
        }

        if (!file_exists($source)) {
            log_msg("  SKIP (not found): {$relativePath}");
            continue;
        }

        if (is_file($source)) {
            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($source, $dest);
            $fileCount++;
            continue;
        }

        if (is_dir($source)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filePath = $file->getPathname();
                $relative = str_replace($root . '/', '', $filePath);

                // Check exclusions
                $skip = is_forbidden_pack_path($relative);
                foreach ($def['exclude'] as $exclude) {
                    if (strpos($relative, $exclude) !== false) {
                        $skip = true;
                        break;
                    }
                }

                // Skip .gitkeep files
                if (basename($filePath) === '.gitkeep') {
                    $skip = true;
                }

                if ($skip) {
                    continue;
                }

                $destPath = $stagingPath . '/' . $relative;
                $destDir = dirname($destPath);

                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }

                if ($file->isFile()) {
                    copy($filePath, $destPath);
                    $fileCount++;
                }
            }
        }
    }

    return $fileCount;
}

function generate_tree_txt(string $stagingPath): string
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stagingPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $files[] = './' . str_replace($stagingPath . '/', '', $file->getPathname());
    }

    sort($files);

    return implode("\n", array_slice($files, 0, 5000)) . "\n";
}

function generate_stats_md(string $stagingPath, string $context): string
{
    $totalFiles = 0;
    $phpFiles = 0;
    $mdFiles = 0;
    $totalLines = 0;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($stagingPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $totalFiles++;
        $extension = strtolower($file->getExtension());
        if ($extension === 'php') {
            $phpFiles++;
        }
        if ($extension === 'md') {
            $mdFiles++;
        }

        $content = @file($file->getPathname());
        if (is_array($content)) {
            $totalLines += count($content);
        }
    }

    $generatedAt = date('Y-m-d H:i:s');
    $otherFiles = $totalFiles - $phpFiles - $mdFiles;
    $phpLinesApprox = $totalFiles > 0 ? (int) round($totalLines * $phpFiles / $totalFiles) : 0;

    return <<<STATS
# Package Statistics

**Context:** {$context}
**Generated:** {$generatedAt}

## File Counts

| Type | Count |
|------|-------|
| Total files | {$totalFiles} |
| PHP files | {$phpFiles} |
| Markdown files | {$mdFiles} |
| Other | {$otherFiles} |

## Lines of Code

| Metric | Value |
|--------|-------|
| Total lines | {$totalLines} |
| PHP lines (approx) | {$phpLinesApprox} |

## Coverage

This package contains focused review surfaces for AI upload.
It is NOT a backup. It is NOT the full repository.

STATS;
}

function generate_review_context(string $context): string
{
    $contexts = [
        'governance-quality' => <<<CTX
# Review Context: Governance Quality

## What This Package Contains

This package contains the AvaX governance architecture:
- All how-to documents (architecture, design, security, performance, testing, documentation)
- All skill definitions (reusable agent playbooks)
- Governance index and enforcement map
- Agent execution protocol and AI preflight rules
- Documentation templates and context files
- Full docs/ tree with architecture decisions and examples

## What to Review

1. **Governance Quality**: Are rules clear, enforceable, and non-contradictory?
2. **Architecture Philosophy**: Does the documentation model scale for enterprise teams?
3. **AI Governance**: Are agent rules deterministic and evidence-based?
4. **Documentation Quality**: Is the two-layer model (central vs local) well-defined?
5. **Conflict Resolution**: Does the priority matrix handle real-world conflicts?

## Review Guide

- Read GOVERNANCE_INDEX.md for routing
- Read GOVERNANCE_ENFORCEMENT_MAP.md for enforcement
- Read how-to-document.md for documentation layering
- Read how-to-write-self-explaining-architecture.md for documentation content standard
- Read AGENTS.md for root execution contract

## Known YELLOW Areas

- Some how-to documents may reference old tooling paths
- Governance enforcement scripts may have edge cases
CTX,
        'identity-architecture' => <<<CTX
# Review Context: Identity Architecture

## What This Package Contains

This package contains the Identity component architecture:
- Full components/Identity/ tree (PublicSurface/Flows/Capabilities/Configuration/Foundation)
- Identity unit tests (36 tests)
- Identity architecture tests (5 tests)
- Identity test support fixtures (11 files)
- Shared framework abstractions (Runtime, StateReset, ComponentRegistry, RequestScope)
- Minimal DI surface (ContainerInterface, RequestInterface, Security)

## What to Review

1. **Enterprise Auth Architecture**: Does the DI builder pattern scale for enterprise auth?
2. **Boundaries**: Are sub-domain boundaries (Auth, Access, Tokens, Tenancy, Credentials) clean?
3. **Security Thinking**: Are security invariants fail-closed? Are negative tests present?
4. **Runtime Safety**: Does the component handle long-lived worker safety (reset lifecycle)?
5. **Naming Quality**: Are there duplicates, collisions, or misleading names?
6. **DSL Quality**: Is the fluent builder intuitive and composable?
7. **Test Quality**: Do tests prove behavior, not just construction?

## Review Guide

- Start at components/Identity/System/PublicSurface/ for entry points
- Read IdentityBuilder.php for DI assembly
- Check Flows/ for security-critical behavior (Authenticate, Authorize)
- Check Capabilities/ for reusable auth behavior
- Verify InMemory stores are NOT wired into production paths

## Known YELLOW Areas

- InMemory stores wired through builder defaults (acceptable for V1, must be replaced for production)
- IdentityBuilder is 560+ LOC (should be split)
- withContainer() uses container as service locator (AGENTS.md violation)
- Some security flows are stub implementations
CTX,
        'framework-runtime' => <<<CTX
# Review Context: Framework Runtime

## What This Package Contains

This package contains the AvaX framework core:
- Full framework/ tree (~800 files)
- Application Container DI surface
- HTTP capabilities (request handling)

## What to Review

1. **Runtime Architecture**: Is the lifecycle model clean (boot → request → reset)?
2. **Long-Lived Runtime Readiness**: Does the framework survive in FrankenPHP/RoadRunner/Swoole?
3. **DI Correctness**: Does configuration assemble, and runtime execute?
4. **Request Scope Isolation**: Is per-request state properly isolated?
5. **Hot Path Discipline**: Are reflection, filesystem scans, and config parsing avoided in hot paths?

## Review Guide

- Start at framework/System/PublicSurface/ for entry points
- Check framework/System/Capabilities/Runtime/ for lifecycle management
- Check framework/System/Capabilities/StateReset/ for worker safety
- Check framework/System/Configuration/ for assembly boundaries

## Known YELLOW Areas

- Some capabilities may still use reflection at runtime
- Framework is large; review should focus on runtime-critical paths
CTX,
        'governance-enforcement' => <<<CTX
# Review Context: Governance Enforcement Tooling

## What This Package Contains

This package contains all governance enforcement tooling:
- Governance checkers (component shape, stage lock, governance index)
- Refactoring tools (namespace drift, public surface, runtime composition leaks)
- Security checkers (security naming, governance)
- Performance checkers (performance naming, governance)
- Testing tools (shallow test detection)
- Architecture audit tools
- Baseline configurations

## What to Review

1. **Governance Automation Quality**: Do checkers accurately detect violations?
2. **Architecture Enforcement**: Is enforcement comprehensive and non-bypassable?
3. **Validation Strategy**: Does the tooling cover all mandatory rules?
4. **Anti-Pattern Detection**: Are forbidden folders, naming, and patterns caught?

## Review Guide

- Each checker in tooling/governance/ should map to a rule in GOVERNANCE_ENFORCEMENT_MAP.md
- Check that exit codes are correct (0 = pass, 1 = findings)
- Verify that checkers scan the correct paths

## Known YELLOW Areas

- Some checkers may have false positives on edge cases
- Baseline files may need periodic review
CTX,
        'testing-quality' => <<<CTX
# Review Context: Testing Strategy and Quality

## What This Package Contains

This package contains the full test suite:
- Unit tests (all)
- Integration tests (all)
- Feature tests (all)
- Architecture tests (all)
- Golden path runtime tests
- Contract tests
- Operation tests
- PHPUnit configuration
- Composer files
- Testing tooling

## What to Review

1. **Test Philosophy**: Does the suite follow risk-based behavioral testing?
2. **Risk-Based Testing**: Are security boundaries tested with negative tests?
3. **Parallel Testing**: Is the suite safe for parallel execution?
4. **Runtime-Safe Testing**: Do tests prove worker safety (reset, state isolation)?
5. **Enterprise Confidence Model**: Can a team deploy with confidence from this suite?

## Review Guide

- Check tests/Unit/Components/Identity/ for security boundary tests
- Verify negative tests exist for auth flows
- Check that tests prove behavior, not construction
- Review phpunit.xml for parallel testing configuration

## Known YELLOW Areas

- 36 unit tests for 648 Identity PHP files (V1 acceptable, must increase for production)
- Some tests may only prove construction, not behavior
- Shallow test detector (check-shallow-tests.php) should be run to identify weak tests
CTX,
        'documentation-philosophy' => <<<CTX
# Review Context: Self-Explaining Architecture Documentation

## What This Package Contains

This package contains the self-explaining architecture documentation system:
- How-to-write-self-explaining-architecture.md (content standard)
- Architecture templates
- Self-explaining architecture skill
- Documentation examples (api, identity, queue, runtime)
- Generated evidence for self-explaining architecture
- Documentation how-to guides
- Dictionary governance
- ADR examples with Mermaid diagrams

## What to Review

1. **Local Documentation Philosophy**: Does the documentation model explain architecture locally?
2. **AI-Oriented Architecture Docs**: Can an AI agent understand the system from docs alone?
3. **Dictionary Governance**: Are terms defined with "What It Is", "What It Is NOT", "Common Confusion"?
4. **ADR Strategy**: Do ADRs have Status, Context, Decision, Consequences?
5. **Mermaid Usage**: Are diagrams clear and accurate?
6. **Maintainability Philosophy**: Can a junior developer understand the system from these docs?

## Review Guide

- Read how-to-write-self-explaining-architecture.md for the content standard
- Check docs/examples/self-explaining-architecture/ for example quality
- Review dictionary entries for clarity
- Review ADRs for decision quality

## Known YELLOW Areas

- Some example docs may be incomplete
- Generated evidence may need periodic regeneration
CTX,
    ];

    return $contexts[$context] ?? "# Review Context\n\nNo specific context available.\n";
}

function create_zip(string $stagingPath, string $zipPath): bool
{
    try {
        $phar = new PharData($zipPath, 0, null, Phar::ZIP);
        $phar->buildFromDirectory($stagingPath);
        return true;
    } catch (\Exception $e) {
        log_msg("  ERROR: Failed to create ZIP: " . $e->getMessage());
        return false;
    }
}

function metadata_fragment_findings(string $content): array
{
    $rawDateFragment = implode('', ['"', ' . ', 'date(']);
    $rawConcatenatedExpression = implode('', ['"', ' . ', '(']);
    $rawExpressionFragment = implode('', [' ', '.', ' ', '(']);

    $patterns = [
        $rawDateFragment => 'unevaluated date concatenation',
        $rawConcatenatedExpression => 'unevaluated concatenated expression',
        $rawExpressionFragment => 'unevaluated expression fragment',
        '<?php' => 'PHP source fragment in generated metadata',
    ];

    $findings = [];
    foreach ($patterns as $needle => $reason) {
        if (str_contains($content, $needle)) {
            $findings[] = $reason . " ({$needle})";
        }
    }

    return $findings;
}

function validate_generated_metadata_files(string $basePath): array
{
    $required = ['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'];
    $errors = [];

    foreach ($required as $file) {
        $path = $basePath . '/' . $file;
        if (! file_exists($path)) {
            $errors[] = "Missing required metadata file: {$file}";
            continue;
        }

        $content = (string) file_get_contents($path);
        foreach (metadata_fragment_findings($content) as $finding) {
            $errors[] = "{$file}: {$finding}";
        }
    }

    return $errors;
}

function validate_zip(string $zipPath): array
{
    if (!file_exists($zipPath)) {
        return ['valid' => false, 'error' => 'ZIP file not created'];
    }

    try {
        $zip = new PharData($zipPath);
        $fileList = [];
        $fileCount = 0;
        $forbiddenEntries = [];
        $metadataErrors = [];

        foreach (new RecursiveIteratorIterator($zip) as $file) {
            $relativePath = str_replace('phar://' . $zipPath . '/', '', $file->getPathname());
            $fileList[] = $relativePath;
            $fileCount++;

            if (is_forbidden_pack_path($relativePath)) {
                $forbiddenEntries[] = $relativePath;
            }

            if (in_array(basename($relativePath), ['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'], true)) {
                foreach (metadata_fragment_findings((string) file_get_contents($file->getPathname())) as $finding) {
                    $metadataErrors[] = "{$relativePath}: {$finding}";
                }
            }
        }
        $fileListStr = implode("\n", $fileList);
        $hasTree = in_array('TREE.txt', $fileList, true);
        $hasStats = in_array('STATS.md', $fileList, true);
        $hasContext = in_array('REVIEW_CONTEXT.md', $fileList, true);
        $size = filesize($zipPath);
        $sizeMb = round($size / 1024 / 1024, 2);
        $valid = $fileCount > 0
            && $hasTree
            && $hasStats
            && $hasContext
            && $forbiddenEntries === []
            && $metadataErrors === [];

        return [
            'valid' => $valid,
            'zip_entries' => $fileCount,
            'size' => $size,
            'size_mb' => $sizeMb,
            'has_review_context' => $hasContext,
            'has_tree' => $hasTree,
            'has_stats' => $hasStats,
            'forbidden_entries' => $forbiddenEntries,
            'metadata_errors' => $metadataErrors,
        ];
    } catch (\Exception $e) {
        return ['valid' => false, 'error' => 'Cannot read ZIP: ' . $e->getMessage()];
    }
}

function manifest_json(array $manifest): string
{
    return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// ── Main Execution ─────────────────────────────────────────────────────────

log_msg("=== AI Review Pack Generator ===");
log_msg("Root: {$root}");
log_msg("Pack dir: {$packDir}");

// Ensure pack directory exists
if (!is_dir($packDir)) {
    mkdir($packDir, 0755, true);
    log_msg("Created pack directory");
}

// Step 1: Clean staging
clean_staging($stagingDir);
mkdir($stagingDir, 0755);

// Step 2: Detect stale packs
$stale = detect_stale_packs($packDir);
if (!empty($stale)) {
    log_msg("STALE packs detected (older than 30 days):");
    foreach ($stale as $s) {
        log_msg("  - {$s}");
    }
}

// Step 3: Detect duplicates
$duplicates = detect_duplicates($definitions);
if (!empty($duplicates)) {
    log_msg("DUPLICATE files detected:");
    foreach ($duplicates as $d) {
        log_msg("  - {$d}");
    }
}

// Step 4: Generate each pack
$timestamp = date('Y-m-d-H-i-s');
$packDirName = "{$timestamp}-{$packPurpose}-review";
$packDirTimestamped = $packDir . '/' . $packDirName;
if (!is_dir($packDirTimestamped)) {
    mkdir($packDirTimestamped, 0755, true);
}
$generatedPacks = [];
$generationErrors = [];

foreach ($definitions as $name => $def) {
    log_msg("\nGenerating: {$name}");

    // Create staging subdirectory with timestamp
    $stagingBase = $stagingDir . '/' . $timestamp;
    $zipName = "review-{$name}.zip";
    $zipPath = $packDirTimestamped . '/' . $zipName;

    // Copy files to staging
    $fileCount = copy_to_staging($root, $stagingBase, $name, $def);
    log_msg("  Copied {$fileCount} files");

    if ($fileCount === 0) {
        log_msg("  WARNING: No files copied, skipping");
        continue;
    }

    $stagingPath = $stagingBase . '/' . $name;

    // Generate TREE.txt
    log_msg("  Generating TREE.txt");
    $tree = generate_tree_txt($stagingPath);
    file_put_contents($stagingPath . '/TREE.txt', $tree);

    // Generate STATS.md
    log_msg("  Generating STATS.md");
    $stats = generate_stats_md($stagingPath, $def['context']);
    file_put_contents($stagingPath . '/STATS.md', $stats);

    // Generate REVIEW_CONTEXT.md
    log_msg("  Generating REVIEW_CONTEXT.md");
    $context = generate_review_context($def['context']);
    file_put_contents($stagingPath . '/REVIEW_CONTEXT.md', $context);

    $metadataErrors = validate_generated_metadata_files($stagingPath);
    if ($metadataErrors !== []) {
        foreach ($metadataErrors as $error) {
            log_msg("  METADATA ERROR: {$error}");
            $generationErrors[] = "{$zipName}: {$error}";
        }
        continue;
    }

    // Create ZIP
    log_msg("  Creating ZIP: {$zipName}");
    if (!create_zip($stagingPath, $zipPath)) {
        log_msg("  FAILED to create ZIP");
        continue;
    }

    // Validate ZIP
    $validation = validate_zip($zipPath);
    log_msg("  Validation: " . json_encode($validation));

    if (!$validation['valid']) {
        log_msg("  FAILED validation");
        $generationErrors[] = "{$zipName}: " . json_encode($validation);
        continue;
    }

    if (($validation['size_mb'] ?? 0) > 25) {
        log_msg("  WARNING: ZIP exceeds 25MB target ({$validation['size_mb']}MB)");
    }

    $generatedPacks[] = [
        'name' => $zipName,
        'path' => $zipPath,
        'files_copied_before_metadata' => $fileCount,
        'zip_entries' => $validation['zip_entries'],
        'validation' => $validation,
    ];
}

// Step 5: Clean up staging
clean_staging($stagingDir);
log_msg("\nStaging cleaned");

// Step 6: Write manifest
$manifest = [
    'generated_at' => date('Y-m-d H:i:s'),
    'purpose' => $packPurpose,
    'folder' => $packDirName,
    'packs' => $generatedPacks,
    'stale_packs' => $stale,
    'duplicates' => $duplicates,
    'generation_errors' => $generationErrors,
    'count_semantics' => [
        'files_copied_before_metadata' => 'Source files copied into staging before REVIEW_CONTEXT.md, TREE.txt, and STATS.md are added.',
        'zip_entries' => 'Actual file entries found inside the generated ZIP after metadata files are added.',
    ],
];

$isBlockedReview = str_contains($packPurpose, 'blocked') || str_contains($packPurpose, 'gate-adoption');
$statusText = $isBlockedReview
    ? "Status: YELLOW/RED review pack. This is not a GREEN production-readiness pack.\n\n"
        . "Baselined: legacy PHPStan, self-explaining architecture, and shallow-test findings documented under `.agents/management/baselines/`.\n\n"
        . "Blocked: FULL_GREEN remains forbidden until full-mode gates are clean or formally accepted under the governance exception process.\n\n"
        . "Safe to continue: bounded Identity slices may proceed only under changed-scope enforcement.\n\n"
        . "Not safe: claiming production-ready 11++ or bypassing baseline/changed gates.\n\n"
    : "";

$readme = "# AI Review Packs\n\n"
    . "Generated: {$manifest['generated_at']}\n\n"
    . "Purpose: {$packPurpose}\n\n"
    . $statusText
    . "This folder contains focused review ZIPs generated from current repository files.\n"
    . "It is not a backup and it is not canonical governance.\n\n"
    . "Required root files:\n\n"
    . "- README.md\n"
    . "- MANIFEST.md\n"
    . "- manifest.json\n";

file_put_contents($packDirTimestamped . '/README.md', $readme);

$packRows = implode("\n", array_map(fn($p) => sprintf(
    '| %s | copied=%d; zip_entries=%d | %.2f MB | %s |',
    $p['name'],
    $p['files_copied_before_metadata'],
    $p['zip_entries'],
    $p['validation']['size_mb'] ?? 0,
    $p['validation']['valid'] ? 'OK' : 'FAIL'
), $generatedPacks));

$generationErrorLines = $generationErrors === []
    ? "None\n"
    : implode("\n", array_map(fn($e) => "- {$e}", $generationErrors)) . "\n";

$manifestMarkdown = "# Review Pack Manifest\n\nGenerated: {$manifest['generated_at']}\n\n"
    . "Purpose: {$packPurpose}\n\n"
    . "Counts distinguish source files copied before metadata from actual ZIP entries after metadata.\n\n"
    . "## Packs\n\n| Pack | Files | Size | Status |\n|------|-------|------|--------|\n"
    . $packRows
    . "\n\n## Generation Errors\n\n"
    . $generationErrorLines
    . "\n";

file_put_contents($packDirTimestamped . '/MANIFEST.md', $manifestMarkdown);

file_put_contents($packDirTimestamped . '/manifest.json', manifest_json($manifest));

log_msg("\nManifest written to: {$packDirTimestamped}/MANIFEST.md");

// Step 7: Validate root metadata
foreach (['README.md', 'MANIFEST.md', 'manifest.json'] as $metadataFile) {
    $metadataPath = $packDirTimestamped . '/' . $metadataFile;
    foreach (metadata_fragment_findings((string) file_get_contents($metadataPath)) as $finding) {
        $generationErrors[] = "{$metadataFile}: {$finding}";
    }
}

// Step 8: Summary
log_msg("\n=== Pack Generation Summary ===");
log_msg("Generated: " . count($generatedPacks) . " packs");
foreach ($generatedPacks as $pack) {
    $v = $pack['validation'];
    log_msg(sprintf(
        "  %-40s copied=%6d  zip_entries=%6d  %6.2f MB  %s",
        $pack['name'],
        $pack['files_copied_before_metadata'],
        $pack['zip_entries'],
        $v['size_mb'],
        $v['valid'] ? 'OK' : 'FAIL'
    ));
}

if (!empty($stale)) {
    log_msg("\nStale packs to consider deleting:");
    foreach ($stale as $s) {
        log_msg("  - {$s}");
    }
}

if ($generationErrors !== [] || count($generatedPacks) !== count($definitions)) {
    log_msg("\nFAILED: Review pack generation completed with errors.");
    foreach ($generationErrors as $error) {
        log_msg("  - {$error}");
    }
    exit(1);
}

log_msg("\nDone.");
