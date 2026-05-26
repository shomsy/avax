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

const GENERATOR_VERSION = '2.0.0';

function usage(): string
{
    return <<<'USAGE'
Usage:
  php tooling/governance/generate-review-packs.php
  php tooling/governance/generate-review-packs.php --purpose=<lowercase-hyphen-name>
  php tooling/governance/generate-review-packs.php --name=<lowercase-hyphen-name>
  php tooling/governance/generate-review-packs.php --purpose=<lowercase-hyphen-name> --name=<lowercase-hyphen-name>

Options:
  --help                         Print this help and exit without generating packs.
  --purpose=<name>               Metadata purpose. Only lowercase letters, numbers, and hyphens.
  --name=<name>                  Run folder name stem. Only lowercase letters, numbers, and hyphens.

Rules:
  If both --purpose and --name are provided, they must be identical.
  Unknown options fail.
  Malformed values fail.
USAGE;
}

function parse_cli_args(array $argv): array
{
    $purpose = getenv('REVIEW_PACK_PURPOSE') ?: 'governance-final-hardening';
    $name = null;

    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];

        if ($arg === '--help' || $arg === '-h') {
            echo usage();
            exit(0);
        }

        if (str_starts_with($arg, '--purpose=')) {
            $purpose = substr($arg, strlen('--purpose='));
            continue;
        }

        if ($arg === '--purpose') {
            $i++;
            if (!isset($argv[$i])) {
                fwrite(STDERR, "Missing value for --purpose.\n");
                exit(1);
            }
            $purpose = $argv[$i];
            continue;
        }

        if (str_starts_with($arg, '--name=')) {
            $name = substr($arg, strlen('--name='));
            continue;
        }

        if ($arg === '--name') {
            $i++;
            if (!isset($argv[$i])) {
                fwrite(STDERR, "Missing value for --name.\n");
                exit(1);
            }
            $name = $argv[$i];
            continue;
        }

        if (str_starts_with($arg, '--')) {
            fwrite(STDERR, "Unknown option: {$arg}\n");
            exit(1);
        }

        // We do not accept positional arguments
        fwrite(STDERR, "Unexpected argument: {$arg}\n");
        exit(1);
    }

    $name ??= $purpose;

    foreach (['purpose' => $purpose, 'name' => $name] as $label => $value) {
        if (!is_string($value) || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value)) {
            fwrite(STDERR, "Invalid {$label}. Use lowercase letters, numbers, and hyphens only.\n");
            exit(1);
        }
    }

    if ($purpose !== $name) {
        fwrite(STDERR, "--purpose and --name must be identical for a single immutable run id.\n");
        exit(1);
    }

    return [
        'purpose' => $purpose,
        'name' => $name,
    ];
}

$cli = parse_cli_args($argv);
$packPurpose = $cli['purpose'];
$packName = $cli['name'];

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

function count_files(string $path): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $count++;
        }
    }

    return $count;
}

function generate_stats_md(
    string $stagingPath,
    string $context,
    string $generatedAt,
    string $purpose,
    string $runId,
    int $filesCopiedBeforeMetadata,
    int $zipEntries
): string
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

    $otherFiles = $totalFiles - $phpFiles - $mdFiles;
    $phpLinesApprox = $totalFiles > 0 ? (int) round($totalLines * $phpFiles / $totalFiles) : 0;

    return <<<STATS
# Package Statistics

**Context:** {$context}
**Generated:** {$generatedAt}
**Purpose:** {$purpose}
**Run ID:** {$runId}
**Files Copied Before Metadata:** {$filesCopiedBeforeMetadata}
**ZIP Entries:** {$zipEntries}

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

function generate_review_context(string $context, array $runMetadata): string
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

    $body = $contexts[$context] ?? "# Review Context\n\nNo specific context available.\n";
    $header = "# Run Metadata\n\n"
        . "- Generated: {$runMetadata['generated_at']}\n"
        . "- Purpose: {$runMetadata['purpose']}\n"
        . "- Run ID: {$runMetadata['run_id']}\n"
        . "- Generator Version: {$runMetadata['generator_version']}\n"
        . "- Git Branch: {$runMetadata['git_branch']}\n"
        . "- Git Commit: {$runMetadata['git_commit']}\n"
        . "- Dirty Status: {$runMetadata['dirty_status']}\n\n"
        . "---\n\n";

    return $header . $body;
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
        '$totalFiles' => 'unevaluated total-files template variable',
        '$generatedAt' => 'unevaluated generated-at template variable',
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

function validate_zip(string $zipPath, array $runMetadata, int $expectedZipEntries, string $expectedRunDir): array
{
    if (!file_exists($zipPath)) {
        return ['valid' => false, 'error' => 'ZIP file not created'];
    }

    if (dirname($zipPath) !== $expectedRunDir) {
        return ['valid' => false, 'error' => 'ZIP path does not belong to current run folder'];
    }

    try {
        $zip = new PharData($zipPath);
        $fileList = [];
        $fileCount = 0;
        $forbiddenEntries = [];
        $metadataErrors = [];
        $statsContent = null;

        foreach (new RecursiveIteratorIterator($zip) as $file) {
            $relativePath = str_replace('phar://' . $zipPath . '/', '', $file->getPathname());
            $fileList[] = $relativePath;
            $fileCount++;

            if (is_forbidden_pack_path($relativePath)) {
                $forbiddenEntries[] = $relativePath;
            }

            if (in_array(basename($relativePath), ['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'], true)) {
                $content = (string) file_get_contents($file->getPathname());
                foreach (metadata_fragment_findings($content) as $finding) {
                    $metadataErrors[] = "{$relativePath}: {$finding}";
                }
                if ($relativePath === 'STATS.md') {
                    $statsContent = $content;
                }
            }
        }
        $hasTree = in_array('TREE.txt', $fileList, true);
        $hasStats = in_array('STATS.md', $fileList, true);
        $hasContext = in_array('REVIEW_CONTEXT.md', $fileList, true);

        if ($fileCount !== $expectedZipEntries) {
            $metadataErrors[] = "ZIP entry count mismatch: expected {$expectedZipEntries}, actual {$fileCount}";
        }

        if ($statsContent === null) {
            $metadataErrors[] = 'STATS.md could not be read from ZIP';
        } else {
            $expectedStats = [
                'Generated' => $runMetadata['generated_at'],
                'Purpose' => $runMetadata['purpose'],
                'Run ID' => $runMetadata['run_id'],
                'ZIP Entries' => (string) $expectedZipEntries,
            ];

            foreach ($expectedStats as $label => $expected) {
                $actual = extract_stats_value($statsContent, $label);
                if ($actual !== $expected) {
                    $metadataErrors[] = "STATS.md {$label} mismatch: expected '{$expected}', actual '" . ($actual ?? 'MISSING') . "'";
                }
            }
        }

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

function command_output(string $command, string $cwd): string
{
    $descriptorSpec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($command, $descriptorSpec, $pipes, $cwd);
    if (!is_resource($process)) {
        return 'UNKNOWN';
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        $value = trim((string) $stderr);
        return $value !== '' ? $value : 'UNKNOWN';
    }

    $value = trim((string) $stdout);
    return $value !== '' ? $value : 'UNKNOWN';
}

function git_output(string $arguments, string $root): string
{
    $git = is_executable('/usr/bin/git') ? '/usr/bin/git' : 'git';

    return command_output($git . ' ' . $arguments, $root);
}

function git_dir_path(string $root): ?string
{
    $gitPath = $root . '/.git';
    if (is_dir($gitPath)) {
        return $gitPath;
    }

    if (!is_file($gitPath)) {
        return null;
    }

    $content = trim((string) file_get_contents($gitPath));
    if (str_starts_with($content, 'gitdir:')) {
        $path = trim(substr($content, strlen('gitdir:')));
        if ($path !== '' && $path[0] !== '/') {
            $path = $root . '/' . $path;
        }

        return $path;
    }

    return null;
}

function git_metadata_from_files(string $root): array
{
    $gitDir = git_dir_path($root);
    if ($gitDir === null || !is_file($gitDir . '/HEAD')) {
        return ['branch' => 'UNKNOWN', 'commit' => 'UNKNOWN'];
    }

    $head = trim((string) file_get_contents($gitDir . '/HEAD'));
    if (!str_starts_with($head, 'ref:')) {
        return ['branch' => 'DETACHED', 'commit' => $head !== '' ? $head : 'UNKNOWN'];
    }

    $ref = trim(substr($head, strlen('ref:')));
    $branch = str_starts_with($ref, 'refs/heads/') ? substr($ref, strlen('refs/heads/')) : $ref;
    $commit = 'UNKNOWN';

    $refFile = $gitDir . '/' . $ref;
    if (is_file($refFile)) {
        $commit = trim((string) file_get_contents($refFile));
    } else {
        $commonDir = $gitDir;
        if (is_file($gitDir . '/commondir')) {
            $common = trim((string) file_get_contents($gitDir . '/commondir'));
            $commonDir = $common[0] === '/' ? $common : $gitDir . '/' . $common;
        }

        foreach ([$commonDir . '/' . $ref, $commonDir . '/packed-refs'] as $candidate) {
            if (!is_file($candidate)) {
                continue;
            }

            if (basename($candidate) !== 'packed-refs') {
                $commit = trim((string) file_get_contents($candidate));
                break;
            }

            foreach (file($candidate, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if ($line[0] === '#' || $line[0] === '^') {
                    continue;
                }
                [$sha, $packedRef] = array_pad(explode(' ', $line, 2), 2, '');
                if ($packedRef === $ref) {
                    $commit = $sha;
                    break 2;
                }
            }
        }
    }

    return [
        'branch' => $branch !== '' ? $branch : 'UNKNOWN',
        'commit' => $commit !== '' ? $commit : 'UNKNOWN',
    ];
}

function dirty_status_summary(string $root): string
{
    $status = git_output('status --short', $root);
    if ($status === 'UNKNOWN' || $status === '' || str_contains($status, 'not found')) {
        return 'UNKNOWN (git status unavailable in PHP runtime)';
    }

    $lines = array_values(array_filter(explode("\n", $status), static fn(string $line): bool => trim($line) !== ''));
    if ($lines === []) {
        return 'clean';
    }

    return count($lines) . ' dirty entries';
}

function extract_stats_value(string $stats, string $label): ?string
{
    if (preg_match('/^\*\*' . preg_quote($label, '/') . ':\*\*\s*(.+)$/m', $stats, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

function validate_existing_pack_folder(string $folder): array
{
    $errors = [];
    foreach (['README.md', 'MANIFEST.md', 'manifest.json'] as $required) {
        if (!is_file($folder . '/' . $required)) {
            $errors[] = "Missing root metadata file: {$required}";
        }
    }

    if ($errors !== []) {
        return $errors;
    }

    $manifestContent = (string) file_get_contents($folder . '/manifest.json');
    $manifest = json_decode($manifestContent, true);
    if (!is_array($manifest)) {
        return ['manifest.json is not valid JSON: ' . json_last_error_msg()];
    }

    foreach (['generated_at', 'purpose', 'run_id', 'packs'] as $field) {
        if (!array_key_exists($field, $manifest)) {
            $errors[] = "manifest.json missing field: {$field}";
        }
    }

    if ($errors !== []) {
        return $errors;
    }

    foreach (['README.md', 'MANIFEST.md', 'manifest.json'] as $metadataFile) {
        $content = (string) file_get_contents($folder . '/' . $metadataFile);
        foreach (metadata_fragment_findings($content) as $finding) {
            $errors[] = "{$metadataFile}: {$finding}";
        }
    }

    $readme = (string) file_get_contents($folder . '/README.md');
    $manifestMd = (string) file_get_contents($folder . '/MANIFEST.md');
    foreach (['generated_at' => 'Generated', 'purpose' => 'Purpose', 'run_id' => 'Run ID'] as $field => $label) {
        $expectedLine = "{$label}: {$manifest[$field]}";
        if (!str_contains($readme, $expectedLine)) {
            $errors[] = "README.md missing '{$expectedLine}'";
        }
        if (!str_contains($manifestMd, $expectedLine)) {
            $errors[] = "MANIFEST.md missing '{$expectedLine}'";
        }
    }

    $runMetadata = [
        'generated_at' => (string) $manifest['generated_at'],
        'purpose' => (string) $manifest['purpose'],
        'run_id' => (string) $manifest['run_id'],
    ];

    foreach ($manifest['packs'] as $pack) {
        if (!is_array($pack) || !isset($pack['name'], $pack['zip_entries'])) {
            $errors[] = 'Invalid pack entry in manifest.json';
            continue;
        }

        $zipPath = $folder . '/' . $pack['name'];
        if (!is_file($zipPath)) {
            $errors[] = "Missing ZIP listed in manifest: {$pack['name']}";
            continue;
        }

        if (dirname($zipPath) !== $folder) {
            $errors[] = "ZIP path is outside current run folder: {$zipPath}";
            continue;
        }

        try {
            $zip = new PharData($zipPath);
            $entries = [];
            $statsContent = null;
            foreach (new RecursiveIteratorIterator($zip) as $file) {
                $relativePath = str_replace('phar://' . $zipPath . '/', '', $file->getPathname());
                $entries[] = $relativePath;

                if (is_forbidden_pack_path($relativePath)) {
                    $errors[] = "{$pack['name']}: forbidden entry {$relativePath}";
                }

                if (basename($relativePath) === 'STATS.md') {
                    $statsContent = (string) file_get_contents($file->getPathname());
                }

                if (in_array(basename($relativePath), ['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'], true)) {
                    foreach (metadata_fragment_findings((string) file_get_contents($file->getPathname())) as $finding) {
                        $errors[] = "{$pack['name']}: {$relativePath}: {$finding}";
                    }
                }
            }
        } catch (\Throwable $e) {
            $errors[] = "{$pack['name']}: cannot read ZIP: {$e->getMessage()}";
            continue;
        }

        foreach (['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'] as $required) {
            if (!in_array($required, $entries, true)) {
                $errors[] = "{$pack['name']}: missing {$required}";
            }
        }

        $actualEntries = count($entries);
        $expectedEntries = (int) $pack['zip_entries'];
        if ($actualEntries !== $expectedEntries) {
            $errors[] = "{$pack['name']}: zip_entries mismatch expected {$expectedEntries}, actual {$actualEntries}";
        }

        if ($statsContent === null) {
            $errors[] = "{$pack['name']}: STATS.md missing or unreadable";
            continue;
        }

        foreach ([
            'Generated' => $runMetadata['generated_at'],
            'Purpose' => $runMetadata['purpose'],
            'Run ID' => $runMetadata['run_id'],
            'ZIP Entries' => (string) $expectedEntries,
        ] as $label => $expected) {
            $actual = extract_stats_value($statsContent, $label);
            if ($actual !== $expected) {
                $errors[] = "{$pack['name']}: STATS.md {$label} mismatch expected '{$expected}', actual '" . ($actual ?? 'MISSING') . "'";
            }
        }
    }

    return $errors;
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
$generatedAt = date('Y-m-d H:i:s');
$packDirName = "{$timestamp}-{$packName}-review";
$packDirTimestamped = $packDir . '/' . $packDirName;
if (!is_dir($packDirTimestamped)) {
    mkdir($packDirTimestamped, 0755, true);
}
$generatedPacks = [];
$generationErrors = [];
$gitMetadata = git_metadata_from_files($root);
$runMetadata = [
    'generated_at' => $generatedAt,
    'purpose' => $packPurpose,
    'run_id' => $packDirName,
    'generator_version' => GENERATOR_VERSION,
    'repo_root' => $root,
    'git_branch' => $gitMetadata['branch'],
    'git_commit' => $gitMetadata['commit'],
    'dirty_status' => dirty_status_summary($root),
];

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

    // Generate REVIEW_CONTEXT.md before STATS.md so the expected ZIP entry
    // count is based on actual unique staging files, not copy operations.
    log_msg("  Generating REVIEW_CONTEXT.md");
    $context = generate_review_context($def['context'], $runMetadata);
    file_put_contents($stagingPath . '/REVIEW_CONTEXT.md', $context);

    $expectedZipEntries = count_files($stagingPath) + 1;

    // Generate STATS.md
    log_msg("  Generating STATS.md");
    $stats = generate_stats_md(
        $stagingPath,
        $def['context'],
        $generatedAt,
        $packPurpose,
        $packDirName,
        $fileCount,
        $expectedZipEntries
    );
    file_put_contents($stagingPath . '/STATS.md', $stats);

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
    $validation = validate_zip($zipPath, $runMetadata, $expectedZipEntries, $packDirTimestamped);
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
    'generated_at' => $runMetadata['generated_at'],
    'purpose' => $packPurpose,
    'run_id' => $packDirName,
    'folder' => $packDirName,
    'name' => $packName,
    'generator_version' => GENERATOR_VERSION,
    'repo_root' => $root,
    'git_branch' => $runMetadata['git_branch'],
    'git_commit' => $runMetadata['git_commit'],
    'dirty_status' => $runMetadata['dirty_status'],
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
    . "Run ID: {$packDirName}\n\n"
    . "Generator Version: " . GENERATOR_VERSION . "\n\n"
    . "Git Branch: {$runMetadata['git_branch']}\n\n"
    . "Git Commit: {$runMetadata['git_commit']}\n\n"
    . "Dirty Status: {$runMetadata['dirty_status']}\n\n"
    . "Validation Status: PENDING\n\n"
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
    . "Run ID: {$packDirName}\n\n"
    . "Generator Version: " . GENERATOR_VERSION . "\n\n"
    . "Git Branch: {$runMetadata['git_branch']}\n\n"
    . "Git Commit: {$runMetadata['git_commit']}\n\n"
    . "Dirty Status: {$runMetadata['dirty_status']}\n\n"
    . "Validation Status: PENDING\n\n"
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

foreach (validate_existing_pack_folder($packDirTimestamped) as $error) {
    $generationErrors[] = "post-generation integrity: {$error}";
}

if ($generationErrors !== []) {
    $manifest['generation_errors'] = $generationErrors;
    $manifest['validation_status'] = 'FAIL';
    file_put_contents($packDirTimestamped . '/manifest.json', manifest_json($manifest));
    foreach (['README.md', 'MANIFEST.md'] as $metadataFile) {
        $metadataPath = $packDirTimestamped . '/' . $metadataFile;
        file_put_contents(
            $metadataPath,
            str_replace('Validation Status: PENDING', 'Validation Status: FAIL', (string) file_get_contents($metadataPath))
        );
    }
} else {
    $manifest['validation_status'] = 'OK';
    file_put_contents($packDirTimestamped . '/manifest.json', manifest_json($manifest));
    foreach (['README.md', 'MANIFEST.md'] as $metadataFile) {
        $metadataPath = $packDirTimestamped . '/' . $metadataFile;
        file_put_contents(
            $metadataPath,
            str_replace('Validation Status: PENDING', 'Validation Status: OK', (string) file_get_contents($metadataPath))
        );
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
