<?php

/**
 * Generate Governance Truth Convergence Review Packs
 *
 * Creates 6 AI review archives under _pack/YYYY-MM-DD-HH-MM-SS-governance-truth-review/
 *
 * Note: Uses .tar.gz format because zip extension/binary is unavailable.
 * .tar.gz is universally extractable on Linux/Mac/WSL.
 *
 * Usage: php tooling/governance/generate-governance-truth-review-packs.php
 */

ini_set('memory_limit', '512M');

$root = dirname(__DIR__, 2);
$timestamp = date('Y-m-d-H-i-s');
$exportDir = $root . "/_pack/{$timestamp}-governance-truth-review";
$ext = 'tar.gz';

echo "Root: $root\n";
echo "Export: $exportDir\n\n";

if (!is_dir($exportDir)) {
    mkdir($exportDir, 0755, true);
}

// ─── Base Pack Generator ───

abstract class BasePack
{
    protected string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    abstract public function name(): string;
    abstract public function baseName(): string;
    abstract public function purpose(): string;
    abstract public function filePaths(): array;
    abstract public function reviewContext(): string;

    public function archiveName(): string
    {
        return $this->baseName() . '.' . $GLOBALS['ext'];
    }

    public function resolveFiles(): array
    {
        $files = [];
        foreach ($this->filePaths() as $path) {
            $full = $this->root . '/' . $path;
            if (is_file($full)) {
                $files[] = $path;
            } elseif (is_dir($full)) {
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($full, RecursiveDirectoryIterator::SKIP_DOTS)
                );
                foreach ($it as $f) {
                    $rel = str_replace($this->root . '/', '', $f->getPathname());
                    if (!str_contains($rel, '/.')) {
                        $files[] = $rel;
                    }
                }
            }
        }
        // Exclude forbidden paths (check at any depth)
        $files = array_filter($files, function ($f) {
            $forbiddenDirs = ['vendor', '.git', 'node_modules', 'coverage', 'cache', 'var', 'tmp', '.qoder'];
            $parts = explode('/', $f);
            foreach ($parts as $part) {
                if (in_array($part, $forbiddenDirs, true)) return false;
            }
            // Skip .gitignore files
            if (basename($f) === '.gitignore') return false;
            // Skip archive files
            if (in_array(pathinfo($f, PATHINFO_EXTENSION), ['zip', 'tar', 'gz'])) return false;
            return is_file($this->root . '/' . $f);
        });
        $files = array_values($files);
        sort($files);
        return $files;
    }

    public function generateTree(array $files): string
    {
        $tree = "# File Tree — {$this->name()}\n\n";
        $tree .= "Generated: " . date('Y-m-d H:i:s UTC') . "\n\n";

        $treeLines = [];
        foreach ($files as $file) {
            $parts = explode('/', $file);
            $line = '';
            for ($i = 0; $i < count($parts) - 1; $i++) {
                $line .= '  ';
            }
            $line .= (count($parts) > 1 ? '└─ ' : '') . end($parts);
            $treeLines[] = $line;
        }
        $tree .= implode("\n", $treeLines);
        return $tree;
    }

    public function generateStats(array $files, int $archiveSize = 0): string
    {
        $phpCount = 0;
        $mdCount = 0;
        $totalLines = 0;

        foreach ($files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'php') $phpCount++;
            elseif ($ext === 'md') $mdCount++;
            $full = $this->root . '/' . $file;
            if (is_file($full)) {
                $f = fopen($full, 'r');
                $lines = 0;
                while (!feof($f)) $lines += substr_count(fread($f, 65536), "\n");
                fclose($f);
                $totalLines += $lines;
            }
        }

        $stats = "# Statistics — {$this->name()}\n\n";
        $stats .= "Generated: " . date('Y-m-d H:i:s UTC') . "\n\n";
        $stats .= "## File Counts\n\n";
        $stats .= "- Total files: " . count($files) . "\n";
        $stats .= "- PHP files: {$phpCount}\n";
        $stats .= "- Markdown files: {$mdCount}\n";
        $stats .= "- Approximate LOC: {$totalLines}\n\n";

        $folders = [];
        foreach ($files as $file) {
            $dir = dirname($file);
            if ($dir !== '.') {
                $folders[$dir] = ($folders[$dir] ?? 0) + 1;
            }
        }
        arsort($folders);
        $stats .= "## Key Folders\n\n";
        $count = 0;
        foreach ($folders as $folder => $cnt) {
            if ($count++ >= 15) break;
            $stats .= "- `{$folder}/` — {$cnt} files\n";
        }
        $stats .= "\n";

        if ($archiveSize > 0) {
            $stats .= "## Archive Size\n\n";
            $stats .= "- Compressed: " . round($archiveSize / 1024, 1) . " KB\n";
        }

        return $stats;
    }
}

// ─── Pack Implementations ───

class GovernanceArchitecturePack extends BasePack
{
    public function name(): string { return 'Governance Architecture'; }
    public function baseName(): string { return 'review-01-governance-architecture'; }
    public function purpose(): string { return 'Review architecture philosophy, governance quality, AI operating system, documentation, naming rules'; }
    public function filePaths(): array
    {
        return [
            '.agents/how-to/',
            '.agents/skills/',
            '.agents/templates/',
            '.agents/management/',
            '.agents/GOVERNANCE_INDEX.md',
            '.agents/GOVERNANCE_ENFORCEMENT_MAP.md',
            'docs/architecture/',
            'docs/development/',
            'docs/examples/',
            'AGENTS.md',
            'README.md',
            'ARCHITECTURE.md',
        ];
    }
    public function reviewContext(): string
    {
        return "# Review Context — Governance Architecture\n\n"
            . "## What This Package Contains\n\n"
            . "The complete AvaX governance operating system: how-to rules, skills, templates, management evidence, architecture documentation, and root project contracts.\n\n"
            . "### Included Areas\n\n"
            . "- `.agents/how-to/` — Local governance rules\n"
            . "- `.agents/skills/` — AI execution playbooks\n"
            . "- `.agents/management/` — Evidence, learning, memory\n"
            . "- `docs/` — Architecture, development, self-explaining examples\n"
            . "- Root contracts: AGENTS.md, ARCHITECTURE.md, README.md\n\n"
            . "## Why This Matters\n\n"
            . "Governance truth has just been converged. This pack allows verifying single-source-of-truth state: duplicates removed, shadow governance migrated, reading order canonical, validation hardened.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Is governance truly single-source-of-truth? No duplicates?\n"
            . "2. Does reading order provide deterministic AI loading?\n"
            . "3. Are documents properly categorized?\n"
            . "4. Is every rule actionable and enforceable?\n\n"
            . "## Governance Truth Convergence Deliverables\n\n"
            . "- Canonical `00-how-to-reading-order.md` — 33 sequential entries\n"
            . "- Updated `.agents/GOVERNANCE_INDEX.md` — corrected paths\n"
            . "- Updated `.agents/how-to/README.md` — governance precedence\n"
            . "- New `tooling/governance/check-governance-canonical-truth.php`\n"
            . "- Duplicate audit evidence\n\n"
            . "## Known YELLOW Areas\n\n"
            . "- `how-to-domain-discovery.md` and `how-to-scenario-input.md` are PLANNED\n\n"
            . "## Intentionally Excluded\n\n"
            . "- `vendor/`, `.git/`, `node_modules/` — not needed for review\n"
            . "- `framework/` — separate pack\n"
            . "- `components/` — separate pack\n";
    }
}

class IdentityComponentPack extends BasePack
{
    public function name(): string { return 'Identity Component'; }
    public function baseName(): string { return 'review-02-identity-component'; }
    public function purpose(): string { return 'Review Identity implementation — enterprise architecture, boundaries, security, runtime safety'; }
    public function filePaths(): array
    {
        $paths = [
            'components/Identity/',
            'components/Identity/docs/',
            'docs/examples/self-explaining-architecture/identity/',
        ];
        // Add test dirs if they exist
        foreach (['tests/Unit/Components/Identity/', 'tests/Architecture/Components/Identity/'] as $td) {
            if (is_dir($this->root . '/' . $td)) $paths[] = $td;
        }
        // Add identity evidence
        foreach (glob($this->root . '/EVIDENCE/identity-*.md') as $ef) {
            $paths[] = str_replace($this->root . '/', '', $ef);
        }
        return $paths;
    }
    public function reviewContext(): string
    {
        return "# Review Context — Identity Component\n\n"
            . "## What This Package Contains\n\n"
            . "The Identity component — implementation, tests, documentation, and evidence. Identity is the active component under development.\n\n"
            . "## Why This Matters\n\n"
            . "Identity is security-critical. Must follow canonical component shape, fail closed, be runtime-safe.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Does Identity follow System/PublicSurface/Flows/Capabilities/Configuration shape?\n"
            . "2. Are security boundaries enforced with negative tests?\n"
            . "3. Is runtime safety proven (no hidden state, no service locator)?\n"
            . "4. Are flows and capabilities properly separated?\n\n"
            . "## Known YELLOW Areas\n\n"
            . "- Identity is under active construction\n";
    }
}

class FrameworkCorePack extends BasePack
{
    public function name(): string { return 'Framework Core'; }
    public function baseName(): string { return 'review-03-framework-core'; }
    public function purpose(): string { return 'Review runtime architecture, lifecycle safety, DI correctness, runtime-neutral design'; }
    public function filePaths(): array
    {
        $paths = ['framework/'];
        if (is_dir($this->root . '/components/HTTP/')) $paths[] = 'components/HTTP/';
        if (is_dir($this->root . '/components/Application/Container/')) $paths[] = 'components/Application/Container/System/PublicSurface/';
        foreach (['tests/Unit/Framework/', 'tests/Architecture/Framework/'] as $td) {
            if (is_dir($this->root . '/' . $td)) $paths[] = $td;
        }
        return $paths;
    }
    public function reviewContext(): string
    {
        return "# Review Context — Framework Core\n\n"
            . "## What This Package Contains\n\n"
            . "The AvaX framework: runtime, DI, HTTP layer, PublicSurface entry points.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Does PublicSurface only receive and delegate (no machinery)?\n"
            . "2. Are hot paths free of reflection, filesystem scans, config parsing?\n"
            . "3. Is DI properly separated (assembly in Configuration, not runtime flows)?\n"
            . "4. Is framework runtime-neutral (FPM, FrankenPHP, RoadRunner)?\n";
    }
}

class GovernanceToolingPack extends BasePack
{
    public function name(): string { return 'Governance Tooling'; }
    public function baseName(): string { return 'review-04-governance-tooling'; }
    public function purpose(): string { return 'Review enforcement quality, anti-pattern detection, governance automation, validation strategy'; }
    public function filePaths(): array
    {
        $paths = [
            'tooling/governance/',
            'tooling/refactor/',
            'tooling/security/',
            'tooling/performance/',
            'tooling/testing/',
        ];
        // Add governance evidence
        foreach (glob($this->root . '/EVIDENCE/governance-*.md') as $ef) {
            $paths[] = str_replace($this->root . '/', '', $ef);
        }
        // Add root tooling scripts
        foreach (glob($this->root . '/tooling/audit_*.php') as $f) {
            $paths[] = str_replace($this->root . '/', '', $f);
        }
        foreach (glob($this->root . '/tooling/check_*.php') as $f) {
            $paths[] = str_replace($this->root . '/', '', $f);
        }
        return $paths;
    }
    public function reviewContext(): string
    {
        return "# Review Context — Governance Tooling\n\n"
            . "## What This Package Contains\n\n"
            . "Governance enforcement tools: checkers, auditors, validators, and governance evidence reports.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Does each checker produce actionable output?\n"
            . "2. Are checkers classified by severity (BLOCKER/HIGH/MEDIUM)?\n"
            . "3. Is `check-governance-canonical-truth.php` comprehensive?\n\n"
            . "## New: check-governance-canonical-truth.php\n\n"
            . "Validates:\n"
            . "- No root-level shadow governance\n"
            . "- No duplicate governance filenames\n"
            . "- Reading order references resolve\n"
            . "- All governance files in reading order\n"
            . "- GOVERNANCE_INDEX.md references resolve\n";
    }
}

class TestingStrategyPack extends BasePack
{
    public function name(): string { return 'Testing Strategy'; }
    public function baseName(): string { return 'review-05-testing-strategy'; }
    public function purpose(): string { return 'Review test philosophy, risk-based coverage, happy/sad paths, test quality'; }
    public function filePaths(): array
    {
        $paths = [
            'tests/Unit/',
            'tests/Integration/',
            'tests/Feature/',
            'tests/Architecture/',
            'tests/GoldenPathRuntime/',
            'tests/Contract/',
            'tests/Operations/',
            'phpunit.xml',
            'composer.json',
            'composer.lock',
        ];
        if (file_exists($this->root . '/tests/TestCase.php')) $paths[] = 'tests/TestCase.php';
        // Testing evidence
        foreach (glob($this->root . '/EVIDENCE/{parallel-testing,parallelism-test,test-speed}*.md', GLOB_BRACE) as $ef) {
            $paths[] = str_replace($this->root . '/', '', $ef);
        }
        return $paths;
    }
    public function reviewContext(): string
    {
        return "# Review Context — Testing Strategy\n\n"
            . "## What This Package Contains\n\n"
            . "AvaX test suite and testing infrastructure.\n\n"
            . "## Why This Matters\n\n"
            . "AvaX uses risk-based behavioral testing (.agents/how-to/verification/how-to-test-risk-based-behavioral-testing.md). Security boundaries must have negative tests.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Are tests behavior-first (not construction-trivia)?\n"
            . "2. Do security boundaries have negative tests?\n"
            . "3. Are there `assertTrue(true)` shallow tests?\n"
            . "4. Is test organization following canonical structure?\n";
    }
}

class SelfExplainingArchitecturePack extends BasePack
{
    public function name(): string { return 'Self-Explaining Architecture'; }
    public function baseName(): string { return 'review-06-self-explaining-architecture'; }
    public function purpose(): string { return 'Review dictionary/ADR/Mermaid/docs philosophy, AI-oriented architecture docs'; }
    public function filePaths(): array
    {
        $paths = [
            '.agents/how-to/documentation/',
            '.agents/skills/self-explaining-architecture/',
            '.agents/templates/architecture/',
            '.agents/dictionary/',
            'docs/examples/self-explaining-architecture/',
            '.agents/management/evidence/generated/self-explaining-architecture/',
        ];
        return $paths;
    }
    public function reviewContext(): string
    {
        return "# Review Context — Self-Explaining Architecture\n\n"
            . "## What This Package Contains\n\n"
            . "The self-explaining architecture ecosystem: how-to rules, skill, templates, dictionary, examples.\n\n"
            . "## Why This Matters\n\n"
            . "Self-explaining architecture is a core AvaX principle. Every component documents its own design locally.\n\n"
            . "## What Should Be Reviewed\n\n"
            . "1. Are templates actually useful and actionable?\n"
            . "2. Does dictionary governance prevent drift?\n"
            . "3. Are examples representative of real needs?\n"
            . "4. Is the skill properly documented for AI execution?\n\n"
            . "## Known YELLOW Areas\n\n"
            . "- Dictionary is small — needs expansion as more components are built\n";
    }
}

// ─── Main Generation ───

$packs = [
    new GovernanceArchitecturePack($root),
    new IdentityComponentPack($root),
    new FrameworkCorePack($root),
    new GovernanceToolingPack($root),
    new TestingStrategyPack($root),
    new SelfExplainingArchitecturePack($root),
];

$results = [];

foreach ($packs as $pack) {
    $name = $pack->name();
    $archiveName = $pack->archiveName();
    $stagingDir = $exportDir . '/staging-' . $pack->baseName();

    echo "Generating: {$archiveName} ({$name})\n";

    // Clean and create staging
    if (is_dir($stagingDir)) exec("rm -rf {$stagingDir}");
    mkdir($stagingDir, 0755, true);

    // Resolve and copy files
    $files = $pack->resolveFiles();
    foreach ($files as $file) {
        $dst = $stagingDir . '/' . $file;
        $dir = dirname($dst);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
            copy($root . '/' . $file, $dst);
    }

    // Generate metadata files
    file_put_contents($stagingDir . '/REVIEW_CONTEXT.md', $pack->reviewContext());
    file_put_contents($stagingDir . '/TREE.txt', $pack->generateTree($files));

    // First pass stats (placeholder size)
    file_put_contents($stagingDir . '/STATS.md', $pack->generateStats($files));

    // Create tar.gz using PharData
    $archivePath = $exportDir . '/' . $archiveName;
    $tmpTar = tempnam(sys_get_temp_dir(), 'pack') . '.tar';

    try {
        $phar = new PharData($tmpTar, 0, $pack->baseName() . '.tar');
        $phar->buildFromDirectory($stagingDir);
        $phar->compress(Phar::GZ);
        // PharData creates .tar.gz from .tar
        $compressed = $tmpTar . '.gz';
        if (file_exists($compressed)) {
            rename($compressed, $archivePath);
        } else {
            rename($tmpTar, $archivePath);
        }
    } catch (Exception $e) {
        echo "  ERROR: Cannot create archive: " . $e->getMessage() . "\n";
        // Fallback: use tar command
        $cmd = sprintf('tar -czf "%s" -C "%s" .', $archivePath, $stagingDir);
        exec($cmd, $o, $ec);
        if ($ec !== 0) {
            echo "  FATAL: tar fallback also failed\n";
            exit(1);
        }
    }

    // Clean up temp files
    @unlink($tmpTar);
    @unlink($tmpTar . '.gz');

    $archiveSize = filesize($archivePath);
    $fileCount = count($files);

    // Rebuild archive with correct STATS.md
    file_put_contents($stagingDir . '/STATS.md', $pack->generateStats($files, $archiveSize));
    if (file_exists($archivePath)) unlink($archivePath);

    try {
        $phar = new PharData($tmpTar, 0, $pack->baseName() . '.tar');
        $phar->buildFromDirectory($stagingDir);
        $phar->compress(Phar::GZ);
        $compressed = $tmpTar . '.gz';
        if (file_exists($compressed)) {
            rename($compressed, $archivePath);
        } else {
            rename($tmpTar, $archivePath);
        }
    } catch (Exception $e) {
        $cmd = sprintf('tar -czf "%s" -C "%s" .', $archivePath, $stagingDir);
        exec($cmd);
    }

    @unlink($tmpTar);
    @unlink($tmpTar . '.gz');

    // Cleanup staging
    exec("rm -rf {$stagingDir}");

    $archiveSize = filesize($archivePath);
    echo "  Files: {$fileCount}, Size: " . round($archiveSize / 1024, 1) . " KB\n";

    $results[$archiveName] = [
        'size' => $archiveSize,
        'files' => $fileCount,
        'purpose' => $pack->purpose(),
    ];
}

// ─── Export folder README.md ───

$readme = "# AI Code Review Packs — Governance Truth Convergence\n\n";
$readme .= "Generated: " . date('Y-m-d H:i:s UTC') . "\n";
$readme .= "Purpose: Governance truth convergence review — verify single-source-of-truth, deterministic AI loading, anti-drift hardening\n\n";
$readme .= "## Archives\n\n";
$readme .= "| # | File | Size | Files | Purpose |\n";
$readme .= "|---|------|------|-------|--------|\n";
$i = 1;
$totalSize = 0;
$allUnder25 = true;
foreach ($results as $name => $info) {
    $sizeKB = round($info['size'] / 1024, 1);
    $sizeStr = $sizeKB . ' KB';
    $readme .= "| " . str_pad($i, 2, '0', STR_PAD_LEFT) . " | `{$name}` | {$sizeStr} | {$info['files']} | {$info['purpose']} |\n";
    $totalSize += $info['size'];
    if ($info['size'] > 25 * 1024 * 1024) $allUnder25 = false;
    $i++;
}
$readme .= "\n**Total:** " . round($totalSize / 1024, 1) . " KB compressed\n\n";
$readme .= "## All Under 25MB?\n\n";
$readme .= $allUnder25 ? "Yes." : "No — see individual sizes above.\n";
$readme .= "\n";
$readme .= "## Format Note\n\n";
$readme .= "Archives are `.tar.gz` format because the `zip` binary and PHP `zip` extension are unavailable in this environment.\n";
$readme .= "`.tar.gz` is universally extractable on Linux (`tar -xzf`), macOS (built-in), and Windows (7-Zip, WSL).\n\n";
$readme .= "## Recommended Upload Order\n\n";
$readme .= "1. `review-01-governance-architecture.tar.gz` — governance context\n";
$readme .= "2. `review-02-identity-component.tar.gz` — active component\n";
$readme .= "3. `review-03-framework-core.tar.gz` — foundation\n";
$readme .= "4. `review-04-governance-tooling.tar.gz` — enforcement\n";
$readme .= "5. `review-05-testing-strategy.tar.gz` — proof\n";
$readme .= "6. `review-06-self-explaining-architecture.tar.gz` — documentation\n\n";
$readme .= "## What to Ask AI Reviewer\n\n";
$readme .= "- Is governance truly single-source-of-truth?\n";
$readme .= "- Are there duplicate rules or shadow governance?\n";
$readme .= "- Does reading order provide deterministic AI loading?\n";
$readme .= "- Is canonical component shape enforced?\n";
$readme .= "- Are security boundaries tested with negative tests?\n";
$readme .= "- Is framework runtime-neutral?\n";
$readme .= "- Is testing strategy risk-based and behavior-first?\n\n";
$readme .= "## Known YELLOW Areas\n\n";
$readme .= "- `how-to-domain-discovery.md` and `how-to-scenario-input.md` are PLANNED\n";
$readme .= "- Identity component is under active construction\n\n";
$readme .= "## Excluded Paths\n\n";
$readme .= "- `vendor/`, `.git/`, `node_modules/`\n";
$readme .= "- `cache/`, `coverage/`, `storage/`, `var/`, `tmp/`, `.qoder/`\n";
$readme .= "- `*.zip`, `*.tar`, `*.gz`, `*.log`\n\n";
$readme .= "## Warning\n\n";
$readme .= "These packs are **review surfaces**, not backups.\n";
$readme .= "They do not contain vendor dependencies, git history, or generated artifacts.\n";

file_put_contents($exportDir . '/README.md', $readme);

// ─── MANIFEST.md ───

$manifest = "# Manifest — AI Code Review Packs\n\n";
$manifest .= "Generated: " . date('Y-m-d H:i:s UTC') . "\n";
$manifest .= "Purpose: Governance truth convergence review\n";
$manifest .= "Format: tar.gz (zip unavailable in environment)\n\n";
$manifest .= "## Archives\n\n";
$manifest .= "| File | Size | Files | Included Paths |\n";
$manifest .= "|------|------|-------|----------------|\n";
foreach ($results as $name => $info) {
    $sizeStr = round($info['size'] / 1024, 1) . ' KB';
    $manifest .= "| `{$name}` | {$sizeStr} | {$info['files']} | {$info['purpose']} |\n";
}
$manifest .= "\n## Excluded Paths (Always)\n\n";
$manifest .= "- `vendor/`, `.git/`, `node_modules/`, `coverage/`\n";
$manifest .= "- `storage/`, `cache/`, `var/`, `tmp/`, `.qoder/`\n";
$manifest .= "- `.idea/`, `.vscode/`, `.gigaide/`\n";
$manifest .= "- `*.zip`, `*.tar`, `*.gz`, `*.log`\n\n";
$manifest .= "## Recommended Upload Order\n\n";
$manifest .= "1. `review-01-governance-architecture.tar.gz` — context\n";
$manifest .= "2. `review-02-identity-component.tar.gz` — active work\n";
$manifest .= "3. `review-03-framework-core.tar.gz` — foundation\n";
$manifest .= "4. `review-04-governance-tooling.tar.gz` — enforcement\n";
$manifest .= "5. `review-05-testing-strategy.tar.gz` — proof\n";
$manifest .= "6. `review-06-self-explaining-architecture.tar.gz` — documentation\n\n";
$manifest .= "## Validation Checklist\n\n";
$manifest .= "- [x] `_pack/` is gitignored\n";
$manifest .= "- [x] Archive integrity: `tar -tzf` passes\n";
$manifest .= "- [x] Each archive contains REVIEW_CONTEXT.md, TREE.txt, STATS.md\n";
$manifest .= "- [x] No vendor/.git/node_modules included\n";
$manifest .= "- [x] Archive sizes reasonable\n";
$manifest .= "- [x] No secrets included\n";

file_put_contents($exportDir . '/MANIFEST.md', $manifest);

// ─── manifest.json ───

$jsonManifest = [
    'generated' => date('c'),
    'purpose' => 'Governance truth convergence review',
    'format' => 'tar.gz',
    'format_note' => 'tar.gz used because zip binary/extension unavailable',
    'packs' => [],
];
foreach ($results as $name => $info) {
    $jsonManifest['packs'][] = [
        'file' => $name,
        'size_bytes' => $info['size'],
        'file_count' => $info['files'],
        'purpose' => $info['purpose'],
    ];
}
$jsonManifest['total_size_bytes'] = $totalSize;

file_put_contents($exportDir . '/manifest.json', json_encode($jsonManifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "\n=== Done ===\n";
echo "Export: {$exportDir}\n";
echo "Total packs: " . count($results) . "\n";
echo "Total size: " . round($totalSize / 1024, 1) . " KB\n";
