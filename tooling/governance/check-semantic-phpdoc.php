<?php

declare(strict_types=1);

/**
 * Semantic PHPDoc ratchet gate.
 *
 * Scans production PHP files for missing or fake semantic PHPDoc. The default
 * mode keeps legacy debt visible as YELLOW ratchet debt while blocking changed
 * production files and baseline regressions.
 *
 * Usage:
 *   php tooling/governance/check-semantic-phpdoc.php
 *   php tooling/governance/check-semantic-phpdoc.php --path=framework/System/PublicSurface
 *   php tooling/governance/check-semantic-phpdoc.php --scope=all --path=tooling/governance/fixtures/semantic-phpdoc
 *   php tooling/governance/check-semantic-phpdoc.php --changed-files=components/Foo/System/PublicSurface/Foo.php
 */

$basePath = getcwd();
$options = parseOptions($argv, $basePath);
$targetPath = resolveTargetPath($basePath, $options['path']);
$includeFixtures = isFixturePath($basePath, $targetPath);
$files = collectPhpFiles($targetPath, $basePath, $includeFixtures);
$changedFiles = changedFiles($basePath, $options['changed-files']);
$baselineCount = baselineCount($options);

$findings = [];
$scanned = 0;
$excluded = 0;
$bannedPhrases = [
    'Handles things',
    'Processes data',
    'Helper for',
    'Service for',
    'Manager for',
    'Does stuff',
    'Utility method',
    'Main method',
    'This class is responsible for everything',
];

foreach ($files as $filePath) {
    $relative = relativePath($basePath, $filePath);

    if (!shouldScanPath($relative, $includeFixtures)) {
        $excluded++;
        continue;
    }

    $content = file_get_contents($filePath);
    if ($content === false || $content === '') {
        continue;
    }

    $scanned++;
    $findings = array_merge($findings, scanFile($relative, $content, $bannedPhrases));
}

$blockingFindings = [];
$legacyFindings = [];
$scope = $options['scope'];

foreach ($findings as $finding) {
    if ($scope === 'all' || isset($changedFiles[$finding['file']])) {
        $blockingFindings[] = $finding;
        continue;
    }

    $legacyFindings[] = $finding;
}

$totalFindings = count($findings);
$blockingCount = count($blockingFindings);
$legacyCount = count($legacyFindings);
$baselineRegression = $baselineCount !== null && $totalFindings > $baselineCount;
$exitCode = ($blockingCount > 0 || $baselineRegression) ? 1 : 0;

echo "Semantic PHPDoc Gate\n";
echo "====================\n\n";
echo "Mode: {$scope}\n";
echo "Scanned files: {$scanned}\n";
echo "Excluded files: {$excluded}\n";
echo "Changed production files: " . count($changedFiles) . "\n";
echo "Violations found: {$totalFindings}\n";
echo "Blocking touched/new violations: {$blockingCount}\n";
echo "Legacy ratchet violations: {$legacyCount}\n";
echo "Baseline violation floor: " . ($baselineCount === null ? 'NOT_SET' : (string) $baselineCount) . "\n\n";

if ($options['show-changed-files']) {
    echo "Changed production file sample:\n";
    foreach (array_slice(array_keys($changedFiles), 0, 50) as $file) {
        echo "- {$file}\n";
    }
    if (count($changedFiles) > 50) {
        echo "... " . (count($changedFiles) - 50) . " more changed production files omitted.\n";
    }
    echo "\n";
}

if ($baselineRegression) {
    echo "[BLOCKER] Semantic PHPDoc findings regressed above the baseline floor ({$totalFindings} > {$baselineCount}).\n\n";
}

if ($blockingCount > 0) {
    echo "Blocking findings:\n";
    foreach ($blockingFindings as $finding) {
        printFinding($finding);
    }
    echo "\n";
}

if ($legacyCount > 0) {
    echo "[YELLOW] Legacy untouched semantic PHPDoc debt remains visible under ratchet.\n";
    echo "Owner: AvaX governance owner\n";
    echo "Target: reduce opportunistically when files are touched; PublicSurface/runtime/security-sensitive files first\n";
    echo "Risk: architecture readability debt; does not block V5.9 unless touched/new scope regresses\n";
    echo "Expiry: next touched-file pass or dedicated documentation hardening phase\n\n";

    if ($options['show-legacy-findings']) {
        echo "Legacy finding sample:\n";
        foreach (array_slice($legacyFindings, 0, 50) as $finding) {
            printFinding($finding);
        }
        if ($legacyCount > 50) {
            echo "... " . ($legacyCount - 50) . " more legacy findings omitted; rerun with a narrower --path for detail.\n";
        }
        echo "\n";
    }
}

if ($totalFindings === 0) {
    echo "PASS - all scanned files have semantic PHPDoc.\n\n";
} elseif ($exitCode === 0) {
    echo "PASS_WITH_YELLOW_RATCHET - no touched/new violations and no baseline regression.\n\n";
} else {
    echo "FAIL - semantic PHPDoc gate found blocking violations.\n\n";
}

echo "Exit code: {$exitCode}\n";
exit($exitCode);

/**
 * @param list<string> $argv
 *
 * @return array{path:string|null,scope:string,changed-files:string|null,baseline:string,baseline-count:int|null,show-legacy-findings:bool,show-changed-files:bool}
 */
function parseOptions(array $argv, string $basePath): array
{
    $options = [
        'path' => null,
        'scope' => 'ratchet',
        'changed-files' => null,
        'baseline' => $basePath . '/EVIDENCE/governance/semantic-phpdoc-ratchet-baseline.md',
        'baseline-count' => null,
        'show-legacy-findings' => false,
        'show-changed-files' => false,
    ];

    foreach (array_slice($argv, 1) as $argument) {
        if (str_starts_with($argument, '--path=')) {
            $options['path'] = substr($argument, 7);
            continue;
        }

        if (str_starts_with($argument, '--scope=')) {
            $scope = substr($argument, 8);
            if (!in_array($scope, ['ratchet', 'all'], true)) {
                fwrite(STDERR, "Unknown scope: {$scope}\n");
                exit(2);
            }
            $options['scope'] = $scope;
            continue;
        }

        if ($argument === '--all' || $argument === '--fail-on-any') {
            $options['scope'] = 'all';
            continue;
        }

        if (str_starts_with($argument, '--changed-files=')) {
            $options['changed-files'] = substr($argument, 16);
            continue;
        }

        if (str_starts_with($argument, '--baseline=')) {
            $options['baseline'] = resolveTargetPath($basePath, substr($argument, 11));
            continue;
        }

        if (str_starts_with($argument, '--baseline-count=')) {
            $options['baseline-count'] = (int) substr($argument, 17);
            continue;
        }

        if ($argument === '--show-legacy-findings') {
            $options['show-legacy-findings'] = true;
            continue;
        }

        if ($argument === '--show-changed-files') {
            $options['show-changed-files'] = true;
            continue;
        }

        if (!str_starts_with($argument, '--') && $options['path'] === null) {
            $options['path'] = $argument;
        }
    }

    return $options;
}

function resolveTargetPath(string $basePath, string|null $path): string
{
    if ($path === null || $path === '') {
        return $basePath;
    }

    if (str_starts_with($path, '/')) {
        return $path;
    }

    return $basePath . '/' . ltrim($path, '/');
}

/**
 * @return list<string>
 */
function collectPhpFiles(string $targetPath, string $basePath, bool $includeFixtures): array
{
    if (is_file($targetPath)) {
        return str_ends_with($targetPath, '.php') ? [$targetPath] : [];
    }

    if (!is_dir($targetPath)) {
        fwrite(STDERR, "Path not found: {$targetPath}\n");
        exit(2);
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetPath, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = relativePath($basePath, $file->getPathname());
        if (shouldScanPath($relative, $includeFixtures)) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function shouldScanPath(string $relative, bool $includeFixtures): bool
{
    if ($includeFixtures && str_starts_with($relative, 'tooling/governance/fixtures/semantic-phpdoc/')) {
        return true;
    }

    if (str_starts_with($relative, '.')) {
        return false;
    }

    foreach ([
        '.git/',
        '.idea/',
        '.agents/',
        '.phpunit.cache/',
        'vendor/',
        'node_modules/',
        'tests/',
        'tooling/',
        'EVIDENCE/',
        'examples/',
        'docs/',
    ] as $prefix) {
        if (str_starts_with($relative, $prefix)) {
            return false;
        }
    }

    if (str_starts_with($relative, 'test_') || in_array($relative, ['scratch.php', 'rector.php'], true)) {
        return false;
    }

    return str_ends_with($relative, '.php');
}

function isFixturePath(string $basePath, string $targetPath): bool
{
    $relative = relativePath($basePath, $targetPath);

    return str_starts_with($relative, 'tooling/governance/fixtures/semantic-phpdoc/');
}

/**
 * @param array{changed-files:string|null} $explicitChangedFiles
 *
 * @return array<string, true>
 */
function changedFiles(string $basePath, string|null $explicitChangedFiles): array
{
    $files = [];

    if ($explicitChangedFiles !== null && $explicitChangedFiles !== '') {
        foreach (explode(',', $explicitChangedFiles) as $file) {
            $relative = trim($file);
            if ($relative !== '') {
                $files[$relative] = true;
            }
        }

        return $files;
    }

    $commands = [
        'git -C ' . escapeshellarg($basePath) . ' diff --name-only --diff-filter=ACMRTUXB HEAD -- 2>/dev/null',
        'git -C ' . escapeshellarg($basePath) . ' ls-files --others --exclude-standard 2>/dev/null',
    ];
    $gitWorked = false;

    foreach ($commands as $command) {
        $output = [];
        $status = 0;
        exec($command, $output, $status);
        if ($status !== 0) {
            continue;
        }
        $gitWorked = true;

        foreach ($output as $line) {
            $relative = trim($line);
            if ($relative === '' || !str_ends_with($relative, '.php') || !shouldScanPath($relative, false)) {
                continue;
            }
            $files[$relative] = true;
        }
    }

    if (!$gitWorked) {
        $files = changedFilesFromGitIndex($basePath);
    }

    ksort($files);

    return $files;
}

/**
 * Detects dirty production PHP files without the git binary.
 *
 * The project PHP wrapper runs inside a container that may not include git.
 * This fallback reads the Git index names and stat data directly. It is a
 * worktree ratchet aid, not a replacement for full Git plumbing.
 *
 * @return array<string, true>
 */
function changedFilesFromGitIndex(string $basePath): array
{
    $tracked = trackedFilesFromGitIndex($basePath);
    if ($tracked === []) {
        return [];
    }

    $changed = [];

    foreach ($tracked as $relative => $metadata) {
        if (!str_ends_with($relative, '.php') || !shouldScanPath($relative, false)) {
            continue;
        }

        $path = $basePath . '/' . $relative;
        if (!is_file($path)) {
            continue;
        }

        $size = filesize($path);
        $mtime = filemtime($path);
        if ($size === false || $mtime === false) {
            continue;
        }

        if ($size !== $metadata['size']) {
            $changed[$relative] = true;
        }
    }

    foreach (collectPhpFiles($basePath, $basePath, false) as $path) {
        $relative = relativePath($basePath, $path);
        if (!shouldScanPath($relative, false) || isset($tracked[$relative])) {
            continue;
        }

        $changed[$relative] = true;
    }

    ksort($changed);

    return $changed;
}

/**
 * @return array<string, array{mtime:int,size:int}>
 */
function trackedFilesFromGitIndex(string $basePath): array
{
    $indexPath = $basePath . '/.git/index';
    if (!is_file($indexPath)) {
        return [];
    }

    $data = file_get_contents($indexPath);
    if ($data === false || strlen($data) < 12 || substr($data, 0, 4) !== 'DIRC') {
        return [];
    }

    $version = unpack('N', substr($data, 4, 4))[1];
    if (!in_array($version, [2, 3], true)) {
        return [];
    }

    $entryCount = unpack('N', substr($data, 8, 4))[1];
    $offset = 12;
    $tracked = [];

    for ($entry = 0; $entry < $entryCount; $entry++) {
        if ($offset + 62 > strlen($data)) {
            break;
        }

        $entryStart = $offset;
        $stat = unpack(
            'Nctime_s/Nctime_n/Nmtime_s/Nmtime_n/Ndev/Nino/Nmode/Nuid/Ngid/Nsize',
            substr($data, $offset, 40)
        );
        $offset += 62;
        $pathEnd = strpos($data, "\0", $offset);
        if ($pathEnd === false) {
            break;
        }

        $path = substr($data, $offset, $pathEnd - $offset);
        $offset = $pathEnd + 1;

        $entryLength = $offset - $entryStart;
        $padding = (8 - ($entryLength % 8)) % 8;
        $offset += $padding;

        if ($path !== '') {
            $tracked[$path] = [
                'mtime' => (int) $stat['mtime_s'],
                'size' => (int) $stat['size'],
            ];
        }
    }

    return $tracked;
}

/**
 * @param array{baseline:string,baseline-count:int|null} $options
 */
function baselineCount(array $options): int|null
{
    if ($options['baseline-count'] !== null) {
        return $options['baseline-count'];
    }

    $baselinePath = $options['baseline'];
    if (!is_file($baselinePath)) {
        return null;
    }

    $content = file_get_contents($baselinePath);
    if ($content === false) {
        return null;
    }

    if (preg_match('/\|\s*Legacy violation baseline\s*\|\s*(\d+)\s*\|/', $content, $match)) {
        return (int) $match[1];
    }

    return null;
}

/**
 * @param list<string> $bannedPhrases
 *
 * @return list<array{file:string,line:int,type:string,severity:string,message:string}>
 */
function scanFile(string $relative, string $content, array $bannedPhrases): array
{
    $tokens = token_get_all($content);
    $findings = [];
    $classBraceStack = [];
    $pendingClass = false;
    $braceDepth = 0;

    foreach ($tokens as $index => $token) {
        if ($token === '{') {
            $braceDepth++;
            if ($pendingClass) {
                $classBraceStack[] = $braceDepth;
                $pendingClass = false;
            }
            continue;
        }

        if ($token === '}') {
            if ($classBraceStack !== [] && end($classBraceStack) === $braceDepth) {
                array_pop($classBraceStack);
            }
            $braceDepth--;
            continue;
        }

        if (!is_array($token)) {
            continue;
        }

        if (isClassLikeToken($token[0]) && !isAnonymousClass($tokens, $index)) {
            $pendingClass = true;
            $docToken = previousDocToken($tokens, $index);
            if ($docToken === null) {
                $findings[] = [
                    'file' => $relative,
                    'line' => $token[2],
                    'type' => 'missing_class_docblock',
                    'severity' => 'HIGH',
                    'message' => 'Production class/interface/trait/enum missing semantic PHPDoc',
                ];
            } else {
                $findings = array_merge($findings, bannedPhraseFindings($relative, $docToken, $bannedPhrases, 'Class docblock'));
            }
            continue;
        }

        if ($token[0] === T_FUNCTION && $classBraceStack !== []) {
            $methodName = methodName($tokens, $index);
            if ($methodName === null || $methodName === '__construct') {
                continue;
            }

            $visibility = methodVisibility($tokens, $index);
            if ($visibility === 'private') {
                continue;
            }

            $docToken = previousDocToken($tokens, $index);
            if ($docToken === null) {
                $findings[] = [
                    'file' => $relative,
                    'line' => $token[2],
                    'type' => 'missing_method_docblock',
                    'severity' => 'HIGH',
                    'message' => ucfirst($visibility) . " method {$methodName}() missing semantic PHPDoc",
                ];
            } else {
                $findings = array_merge($findings, bannedPhraseFindings($relative, $docToken, $bannedPhrases, 'Method docblock'));
            }
        }
    }

    return $findings;
}

function isClassLikeToken(int $tokenId): bool
{
    return $tokenId === T_CLASS
        || $tokenId === T_INTERFACE
        || $tokenId === T_TRAIT
        || (defined('T_ENUM') && $tokenId === T_ENUM);
}

/**
 * @param array<int, mixed> $tokens
 */
function isAnonymousClass(array $tokens, int $classIndex): bool
{
    for ($index = $classIndex - 1; $index >= 0; $index--) {
        $token = $tokens[$index];
        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return is_array($token) && $token[0] === T_NEW;
    }

    return false;
}

/**
 * @param array<int, mixed> $tokens
 *
 * @return array{id:int,text:string,line:int}|null
 */
function previousDocToken(array $tokens, int $index): array|null
{
    for ($cursor = $index - 1; $cursor >= 0; $cursor--) {
        $token = $tokens[$cursor];
        if (is_array($token) && in_array($token[0], [
            T_WHITESPACE,
            T_COMMENT,
            T_PUBLIC,
            T_PROTECTED,
            T_PRIVATE,
            T_STATIC,
            T_ABSTRACT,
            T_FINAL,
            defined('T_READONLY') ? T_READONLY : -1,
        ], true)) {
            continue;
        }

        if (is_array($token) && $token[0] === T_DOC_COMMENT) {
            return [
                'id' => $token[0],
                'text' => $token[1],
                'line' => $token[2],
            ];
        }

        return null;
    }

    return null;
}

/**
 * @param array<int, mixed> $tokens
 */
function methodName(array $tokens, int $functionIndex): string|null
{
    for ($cursor = $functionIndex + 1; $cursor < count($tokens); $cursor++) {
        $token = $tokens[$cursor];
        if (is_array($token) && $token[0] === T_WHITESPACE) {
            continue;
        }
        if ($token === '&') {
            continue;
        }
        if (is_array($token) && $token[0] === T_STRING) {
            return $token[1];
        }

        return null;
    }

    return null;
}

/**
 * @param array<int, mixed> $tokens
 */
function methodVisibility(array $tokens, int $functionIndex): string
{
    for ($cursor = $functionIndex - 1; $cursor >= 0; $cursor--) {
        $token = $tokens[$cursor];

        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT, T_STATIC, T_ABSTRACT, T_FINAL], true)) {
            continue;
        }

        if (is_array($token) && $token[0] === T_PUBLIC) {
            return 'public';
        }

        if (is_array($token) && $token[0] === T_PROTECTED) {
            return 'protected';
        }

        if (is_array($token) && $token[0] === T_PRIVATE) {
            return 'private';
        }

        if ($token === '{' || $token === ';' || $token === '}') {
            return 'public';
        }
    }

    return 'public';
}

/**
 * @param array{id:int,text:string,line:int} $docToken
 * @param list<string> $bannedPhrases
 *
 * @return list<array{file:string,line:int,type:string,severity:string,message:string}>
 */
function bannedPhraseFindings(string $relative, array $docToken, array $bannedPhrases, string $label): array
{
    $findings = [];

    foreach ($bannedPhrases as $phrase) {
        if (stripos($docToken['text'], $phrase) !== false) {
            $findings[] = [
                'file' => $relative,
                'line' => $docToken['line'],
                'type' => 'fake_docblock',
                'severity' => 'HIGH',
                'message' => "{$label} contains banned phrase: '{$phrase}'",
            ];
        }
    }

    return $findings;
}

/**
 * @param array{file:string,line:int,type:string,severity:string,message:string} $finding
 */
function printFinding(array $finding): void
{
    echo "[{$finding['severity']}] {$finding['file']}:{$finding['line']} - {$finding['message']}\n";
}

function relativePath(string $basePath, string $path): string
{
    $normalizedBase = rtrim(str_replace('\\', '/', $basePath), '/') . '/';
    $normalizedPath = str_replace('\\', '/', $path);

    if (str_starts_with($normalizedPath, $normalizedBase)) {
        return substr($normalizedPath, strlen($normalizedBase));
    }

    return ltrim($normalizedPath, '/');
}
