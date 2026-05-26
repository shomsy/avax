#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/sdlc/GitChangedFiles.php';

$root = SdlcRuntime::root();
$purpose = 'engineering-canon';
$timestamp = date('Y-m-d-H-i-s');
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--purpose=')) {
        $purpose = substr($arg, 10);
    }
    if (str_starts_with($arg, '--timestamp=')) {
        $timestamp = substr($arg, 12);
    }
}

if (! preg_match('/^[a-z0-9-]+$/', $purpose)) {
    echo "RED: --purpose must contain only lowercase letters, numbers, and hyphens.\n";
    exit(1);
}
if (! preg_match('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}$/', $timestamp)) {
    echo "RED: --timestamp must use YYYY-MM-DD-HH-MM-SS format.\n";
    exit(1);
}

SdlcRuntime::printRuntimeHeader('Actual Changes Review Pack');
$git = escapeshellarg(SdlcRuntime::requireGit());
$runId = $timestamp.'-'.$purpose.'-actual-changes-review';
$packDir = $root.'/_pack/'.$runId;
$tarPath = $root.'/_pack/'.$runId.'.tar.gz';
$zipPath = $root.'/_pack/'.$runId.'.zip';

if (is_dir($packDir) || file_exists($tarPath) || file_exists($zipPath)) {
    echo "RED: Pack output already exists for run id {$runId}.\n";
    exit(1);
}

foreach (['metadata', 'files', 'patches', 'validation'] as $dir) {
    mkdir($packDir.'/'.$dir, 0775, true);
}

function commandOutput(string $command, string $root): string
{
    $result = SdlcRuntime::runCommand($command, $root);

    return $result['output'].($result['output'] !== '' ? "\n" : '');
}

function lines(string $value): array
{
    $items = [];
    foreach (preg_split('/\R/', trim($value)) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $items[$line] = true;
        }
    }

    return array_keys($items);
}

function isSkippedPath(string $path): bool
{
    if (
        str_starts_with($path, '_pack/')
        || str_contains($path, '_pack/')
        || str_starts_with($path, 'vendor/')
        || str_starts_with($path, '.git/')
        || str_starts_with($path, 'node_modules/')
        || str_starts_with($path, 'cache/')
        || str_starts_with($path, 'coverage/')
        || str_starts_with($path, 'tmp/')
        || str_starts_with($path, '.qoder/')
        || $path === '.env'
        || fnmatch('*.pem', $path)
        || fnmatch('*.key', $path)
        || fnmatch('*.crt', $path)
        || fnmatch('*.p12', $path)
        || fnmatch('*.pfx', $path)
        || str_contains($path, 'engineering-canon-actual-changes-review')
        || str_contains($path, 'engineering-canon-11plusplus-actual-changes-review')
    ) {
        return true;
    }

    return false;
}

function writeFile(string $path, string $content): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    file_put_contents($path, $content);
}

function copyFileToPack(string $root, string $packDir, string $relative): void
{
    $source = $root.'/'.$relative;
    $target = $packDir.'/files/'.$relative;
    $dir = dirname($target);
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    if (! copy($source, $target)) {
        echo "RED: Failed to copy {$relative}\n";
        exit(1);
    }
}

$statusShort = commandOutput($git.' status --short', $root);
$statusPorcelain = commandOutput($git.' status --porcelain=v1', $root);
$diffNameOnly = commandOutput($git.' diff --name-only', $root);
$diffCachedNameOnly = commandOutput($git.' diff --cached --name-only', $root);
$untracked = commandOutput($git.' ls-files --others --exclude-standard', $root);

$all = [];
foreach ([lines($diffNameOnly), lines($diffCachedNameOnly), lines($untracked)] as $list) {
    foreach ($list as $file) {
        $all[$file] = true;
    }
}
$allFiles = array_keys($all);
sort($allFiles);

$copied = [];
$skipped = [];
$deleted = [];
foreach ($allFiles as $file) {
    if (isSkippedPath($file)) {
        $skipped[] = $file;
        continue;
    }
    if (! is_file($root.'/'.$file)) {
        $deleted[] = $file;
        continue;
    }
    copyFileToPack($root, $packDir, $file);
    $copied[] = 'files/'.$file;
}

$repoInfo = implode("\n", [
    'pwd',
    $root,
    'git branch --show-current',
    commandOutput($git.' branch --show-current', $root),
    'git rev-parse HEAD',
    commandOutput($git.' rev-parse HEAD', $root),
    'git rev-parse --short HEAD',
    commandOutput($git.' rev-parse --short HEAD', $root),
    'git status --short',
    $statusShort,
    'git log --oneline -5',
    commandOutput($git.' log --oneline -5', $root),
]);

writeFile($packDir.'/metadata/git-status-short.txt', $statusShort);
writeFile($packDir.'/metadata/git-status-porcelain-v1.txt', $statusPorcelain);
writeFile($packDir.'/metadata/git-diff-name-only.txt', $diffNameOnly);
writeFile($packDir.'/metadata/git-diff-cached-name-only.txt', $diffCachedNameOnly);
writeFile($packDir.'/metadata/git-untracked-files.txt', $untracked);
writeFile($packDir.'/metadata/all-changed-and-untracked-files.txt', implode("\n", $allFiles)."\n");
writeFile($packDir.'/metadata/copied-files.txt', implode("\n", $copied)."\n");
writeFile($packDir.'/metadata/skipped-files.txt', implode("\n", $skipped)."\n");
writeFile($packDir.'/metadata/deleted-files.txt', implode("\n", $deleted)."\n");
writeFile($packDir.'/metadata/repo-info.txt', $repoInfo);
writeFile($packDir.'/metadata/tree-files.txt', commandOutput('find files -type f | sort', $packDir));

writeFile($packDir.'/patches/git-diff.patch', commandOutput($git.' diff', $root));
writeFile($packDir.'/patches/git-diff-cached.patch', commandOutput($git.' diff --cached', $root));
writeFile($packDir.'/patches/git-diff-stat.txt', commandOutput($git.' diff --stat', $root));
writeFile($packDir.'/patches/git-diff-cached-stat.txt', commandOutput($git.' diff --cached --stat', $root));

$expected = [
    '.agents/knowledge/README.md',
    '.agents/knowledge/engineering-canon.md',
    '.agents/knowledge/book-to-rule-traceability.md',
    '.agents/knowledge/source-principles/README.md',
    '.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md',
    '.agents/knowledge/source-principles/clean-code-code-complete.md',
    '.agents/knowledge/source-principles/designing-data-intensive-applications.md',
    '.agents/knowledge/source-principles/domain-driven-design.md',
    '.agents/knowledge/source-principles/patterns-of-enterprise-application-architecture.md',
    '.agents/knowledge/source-principles/pragmatic-programmer.md',
    '.agents/knowledge/source-principles/software-architecture-hard-parts.md',
    '.agents/knowledge/source-principles/use-cases-domain-storytelling.md',
    '.agents/how-to/modeling/how-to-scenario-input.md',
    '.agents/how-to/modeling/how-to-domain-discovery.md',
    '.agents/how-to/architecture/how-to-coupling-governance.md',
    '.agents/how-to/architecture/how-to-architecture-fitness-functions.md',
    '.agents/how-to/architecture/how-to-data-correctness.md',
    '.agents/how-to/architecture/how-to-adr-tradeoff-governance.md',
    '.agents/how-to/architecture/how-to-enterprise-application-patterns.md',
    '.agents/how-to/implementation/how-to-software-construction.md',
    '.agents/how-to/implementation/how-to-refactoring.md',
    '.agents/how-to/implementation/how-to-design-patterns.md',
    '.agents/how-to/runtime/how-to-concurrency-runtime-safety.md',
    '.agents/how-to/verification/how-to-antipattern-detection.md',
    '.agents/how-to/verification/how-to-sdlc-automation.md',
    '.agents/how-to/verification/how-to-sdlc-runners.md',
    '.agents/how-to/verification/how-to-create-ai-code-review-packs.md',
    '.agents/skills/engineering-canon/SKILL.md',
    '.agents/skills/sdlc-automation/SKILL.md',
    '.agents/templates/evidence/scenario-input.md',
    '.agents/templates/evidence/domain-discovery.md',
    '.agents/templates/evidence/coupling-decision.md',
    '.agents/templates/evidence/architecture-fitness-functions.md',
    '.agents/templates/evidence/antipattern-review.md',
    '.agents/templates/evidence/construction-checklist.md',
    '.agents/templates/evidence/refactoring-safety.md',
    '.agents/templates/evidence/pattern-decision.md',
    '.agents/templates/evidence/enterprise-application-boundary.md',
    '.agents/templates/evidence/data-correctness.md',
    '.agents/templates/evidence/adr-tradeoff-decision.md',
    '.agents/templates/evidence/runtime-concurrency-safety.md',
    '.agents/dictionary/antipatterns/blob-god-object.md',
    '.agents/dictionary/antipatterns/fake-abstraction.md',
    '.agents/dictionary/antipatterns/service-locator.md',
    '.agents/dictionary/antipatterns/architecture-theater.md',
    '.agents/dictionary/antipatterns/golden-hammer.md',
    '.agents/dictionary/antipatterns/shallow-tests.md',
    '.agents/dictionary/antipatterns/analysis-paralysis.md',
    '.agents/dictionary/antipatterns/cut-and-paste-programming.md',
    '.agents/dictionary/antipatterns/generic-bucket.md',
    '.agents/dictionary/antipatterns/spaghetti-code.md',
    '.agents/dictionary/antipatterns/stovepipe-system.md',
    'tooling/governance/check-engineering-canon-traceability.php',
    'tooling/governance/check-scenario-input.php',
    'tooling/governance/check-coupling-decisions.php',
    'tooling/governance/check-architecture-fitness-functions.php',
    'tooling/governance/check-antipatterns.php',
    'tooling/governance/check-data-correctness-evidence.php',
    'tooling/governance/check-enterprise-application-boundaries.php',
    'tooling/governance/check-adr-tradeoff-evidence.php',
    'tooling/governance/check-runtime-concurrency-safety.php',
    'tooling/governance/check-refactoring-safety.php',
    'tooling/governance/check-construction-checklist.php',
    'tooling/governance/create-actual-changes-review-pack.php',
    'tooling/sdlc/SdlcRuntime.php',
    'tooling/sdlc/GitChangedFiles.php',
    'tooling/sdlc/preflight.php',
    'tooling/sdlc/validate-changed.php',
    'tooling/sdlc/validate-governance.php',
    'tooling/sdlc/validate-agent-task.php',
    'tooling/sdlc/run-sdlc',
    'composer.json',
];

writeFile($packDir.'/validation/expected-engineering-canon-files.txt', implode("\n", $expected)."\n");
$presence = ["# Expected Engineering Canon Presence", ''];
$missing = 0;
foreach ($expected as $file) {
    $exists = is_file($root.'/'.$file);
    $packed = is_file($packDir.'/files/'.$file);
    if (! $exists || ! $packed) {
        $missing++;
    }
    $presence[] = "- {$file}: repo=".($exists ? 'YES' : 'NO').'; pack='.($packed ? 'YES' : 'NO');
}
writeFile($packDir.'/validation/expected-engineering-canon-presence.md', implode("\n", $presence)."\n");

// Write placeholders first for validation files so they are included in sha256sums
writeFile($packDir.'/validation/tar-list.txt', "PENDING\n");
writeFile($packDir.'/validation/zip-test.txt', "PENDING\n");
writeFile($packDir.'/validation/zip-list.txt', "PENDING\n");

// Write metadata README
$branch = trim(commandOutput($git.' branch --show-current', $root));
$fullCommit = trim(commandOutput($git.' rev-parse HEAD', $root));
$shortCommit = trim(commandOutput($git.' rev-parse --short HEAD', $root));

$readme = <<<MD
# Engineering Canon Actual Changes Review Pack

Purpose: actual changes review pack for Engineering Canon Convergence.

Branch: {$branch}
Full Commit: {$fullCommit}
Short Commit: {$shortCommit}
Generated: {$timestamp}

This is not a normal review pack and not canonical governance.
This pack intentionally includes untracked changed files.
Old `_pack` folders and top-level actual-changes archive files are excluded.

Final archives:

- {$tarPath}
- {$zipPath}

MD;
writeFile($packDir.'/metadata/README.md', $readme);

// Create preliminary archives for validation
$createTar = 'tar -czf '.escapeshellarg($tarPath).' -C '.escapeshellarg($root.'/_pack').' '.escapeshellarg($runId);
$createZip = 'cd '.escapeshellarg($root.'/_pack').' && zip -qr '.escapeshellarg($runId.'.zip').' '.escapeshellarg($runId);
foreach ([$createTar, $createZip] as $command) {
    $result = SdlcRuntime::runCommand($command, $root);
    if ($result['exit_code'] !== 0) {
        echo "RED: Preliminary archive command failed: {$command}\n";
        echo $result['output']."\n";
        exit(1);
    }
}

// Generate the actual validation files from preliminary archives
writeFile($packDir.'/validation/tar-list.txt', commandOutput('tar -tzf '.escapeshellarg($tarPath), $root));
writeFile($packDir.'/validation/zip-test.txt', commandOutput('unzip -t '.escapeshellarg($zipPath), $root));
writeFile($packDir.'/validation/zip-list.txt', commandOutput('unzip -l '.escapeshellarg($zipPath), $root));

// Generate final sha256sums covering validation files and everything else
$hashes = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($packDir, FilesystemIterator::SKIP_DOTS));
foreach ($rii as $fileInfo) {
    $path = $fileInfo->getPathname();
    if (! $fileInfo->isFile() || str_ends_with($path, '/metadata/sha256sums.txt')) {
        continue;
    }
    $relative = substr($path, strlen($packDir) + 1);
    $hashes[] = hash_file('sha256', $path).'  '.$relative;
}
sort($hashes);
writeFile($packDir.'/metadata/sha256sums.txt', implode("\n", $hashes)."\n");

// Rebuild final archives to ensure they contain correct validation files and sha256sums.txt
foreach ([$createTar, $createZip] as $command) {
    $result = SdlcRuntime::runCommand($command, $root);
    if ($result['exit_code'] !== 0) {
        echo "RED: Final archive command failed: {$command}\n";
        echo $result['output']."\n";
        exit(1);
    }
}

// Validate final archives to confirm integrity
$validateTar = SdlcRuntime::runCommand('tar -tzf '.escapeshellarg($tarPath), $root);
$validateZip = SdlcRuntime::runCommand('unzip -t '.escapeshellarg($zipPath), $root);
if ($validateTar['exit_code'] !== 0 || $validateZip['exit_code'] !== 0) {
    echo "RED: Final archive validation failed.\n";
    exit(1);
}

echo "GREEN: Actual changes review pack created.\n";
echo "pack_folder={$packDir}\n";
echo "tar={$tarPath}\n";
echo "zip={$zipPath}\n";
echo "expected_total=".count($expected)."\n";
echo "copied_files=".count($copied)."\n";
echo "skipped_files=".count($skipped)."\n";
echo "missing_expected={$missing}\n";
exit($missing === 0 ? 0 : 1);
