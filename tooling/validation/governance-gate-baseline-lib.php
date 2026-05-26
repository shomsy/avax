<?php

declare(strict_types=1);

/**
 * Shared helpers for phased governance gate adoption.
 *
 * The helpers keep full mode strict while allowing legacy findings to be
 * tracked as explicit baseline debt during adoption.
 */

function avax_gate_arg_value(array $argv, string $name, ?string $default = null): ?string
{
    foreach ($argv as $arg) {
        if ($arg === $name) {
            return '1';
        }

        $prefix = $name . '=';
        if (str_starts_with($arg, $prefix)) {
            return substr($arg, strlen($prefix));
        }
    }

    return $default;
}

function avax_gate_mode(array $argv): string
{
    $mode = avax_gate_arg_value($argv, '--mode', 'full');
    if (!in_array($mode, ['full', 'baseline', 'changed'], true)) {
        fwrite(STDERR, "Invalid --mode={$mode}. Expected full, baseline, or changed.\n");
        exit(2);
    }

    return $mode;
}

function avax_gate_normalize_path(string $root, string $path): string
{
    $path = str_replace('\\', '/', $path);
    $root = rtrim(str_replace('\\', '/', $root), '/') . '/';

    if (str_starts_with($path, $root)) {
        $path = substr($path, strlen($root));
    }

    while (str_starts_with($path, './')) {
        $path = substr($path, 2);
    }

    return ltrim($path, '/');
}

function avax_gate_run_lines(string $command, string $root): array
{
    $output = shell_exec('git -C ' . escapeshellarg($root) . ' ' . $command . ' 2>/dev/null');
    if (!is_string($output) || trim($output) === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode("\n", $output)), static fn(string $line): bool => $line !== ''));
}

function avax_gate_changed_files(string $root): array
{
    $files = array_merge(
        avax_gate_run_lines('diff --name-only', $root),
        avax_gate_run_lines('diff --cached --name-only', $root),
        avax_gate_run_lines('ls-files --others --exclude-standard', $root)
    );

    if ($files === []) {
        $files = avax_gate_changed_files_from_git_metadata($root);
    }

    $normalized = [];
    foreach ($files as $file) {
        $normalized[] = avax_gate_normalize_path($root, $file);
    }

    $normalized = array_values(array_unique($normalized));
    sort($normalized);

    return $normalized;
}

function avax_gate_changed_files_from_git_metadata(string $root): array
{
    $gitDir = avax_gate_git_dir($root);
    if ($gitDir === null) {
        return [];
    }

    $indexEntries = avax_gate_read_git_index($gitDir);
    if ($indexEntries === []) {
        return [];
    }

    $headTree = avax_gate_read_head_tree($gitDir);
    if ($headTree !== [] && count($headTree) < (int) floor(count($indexEntries) / 2)) {
        // Packed-object partial reads are worse than no HEAD comparison.
        // The worktree-vs-index comparison still detects unstaged changes and
        // untracked files, while avoiding a fake "everything changed" result.
        $headTree = [];
    }
    $changed = [];

    foreach ($indexEntries as $path => $indexSha) {
        $absolutePath = $root . '/' . $path;
        if (!file_exists($absolutePath)) {
            $changed[] = $path;
            continue;
        }

        if (is_file($absolutePath) && avax_gate_git_blob_sha((string) file_get_contents($absolutePath)) !== $indexSha) {
            $changed[] = $path;
            continue;
        }

        if ($headTree !== [] && ($headTree[$path] ?? null) !== $indexSha) {
            $changed[] = $path;
        }
    }

    $tracked = array_fill_keys(array_keys($indexEntries), true);
    foreach (avax_gate_list_worktree_files($root) as $path) {
        if (!isset($tracked[$path])) {
            $changed[] = $path;
        }
    }

    $changed = array_values(array_unique($changed));
    sort($changed);

    return $changed;
}

function avax_gate_git_dir(string $root): ?string
{
    $gitPath = $root . '/.git';
    if (is_dir($gitPath)) {
        return $gitPath;
    }

    if (is_file($gitPath)) {
        $content = trim((string) file_get_contents($gitPath));
        if (str_starts_with($content, 'gitdir:')) {
            $dir = trim(substr($content, strlen('gitdir:')));
            if (!str_starts_with($dir, '/')) {
                $dir = $root . '/' . $dir;
            }

            return is_dir($dir) ? $dir : null;
        }
    }

    return null;
}

function avax_gate_read_git_index(string $gitDir): array
{
    $indexPath = $gitDir . '/index';
    if (!is_file($indexPath)) {
        return [];
    }

    $data = (string) file_get_contents($indexPath);
    if (strlen($data) < 12 || substr($data, 0, 4) !== 'DIRC') {
        return [];
    }

    $header = unpack('Nversion/Ncount', substr($data, 4, 8));
    $count = (int) ($header['count'] ?? 0);
    $offset = 12;
    $entries = [];

    for ($i = 0; $i < $count; $i++) {
        if ($offset + 62 > strlen($data)) {
            break;
        }

        $entryStart = $offset;
        $sha = bin2hex(substr($data, $offset + 40, 20));
        $flags = unpack('nflags', substr($data, $offset + 60, 2));
        $pathLength = (int) (($flags['flags'] ?? 0) & 0x0fff);
        $offset += 62;

        if ($pathLength === 0x0fff) {
            $end = strpos($data, "\0", $offset);
            if ($end === false) {
                break;
            }
            $path = substr($data, $offset, $end - $offset);
            $offset = $end + 1;
        } else {
            $path = substr($data, $offset, $pathLength);
            $offset += $pathLength + 1;
        }

        $entryLength = $offset - $entryStart;
        $padding = (8 - ($entryLength % 8)) % 8;
        $offset += $padding;

        if ($path !== '') {
            $entries[str_replace('\\', '/', $path)] = $sha;
        }
    }

    return $entries;
}

function avax_gate_git_blob_sha(string $content): string
{
    return sha1('blob ' . strlen($content) . "\0" . $content);
}

function avax_gate_read_head_tree(string $gitDir): array
{
    $headPath = $gitDir . '/HEAD';
    if (!is_file($headPath)) {
        return [];
    }

    $commonDir = avax_gate_git_common_dir($gitDir);
    $head = trim((string) file_get_contents($headPath));
    $commitSha = $head;
    if (str_starts_with($head, 'ref:')) {
        $ref = trim(substr($head, strlen('ref:')));
        $refPath = $gitDir . '/' . $ref;
        if (!is_file($refPath)) {
            $refPath = $commonDir . '/' . $ref;
        }
        if (!is_file($refPath)) {
            return [];
        }
        $commitSha = trim((string) file_get_contents($refPath));
    }

    $commit = avax_gate_read_git_object($gitDir, $commitSha);
    if ($commit === null || $commit['type'] !== 'commit') {
        return [];
    }

    if (!preg_match('/^tree\s+([0-9a-f]{40})/m', $commit['content'], $matches)) {
        return [];
    }

    return avax_gate_read_tree_recursive($gitDir, $matches[1]);
}

function avax_gate_read_git_object(string $gitDir, string $sha): ?array
{
    if (!preg_match('/^[0-9a-f]{40}$/', $sha)) {
        return null;
    }

    $commonDir = avax_gate_git_common_dir($gitDir);
    $path = $commonDir . '/objects/' . substr($sha, 0, 2) . '/' . substr($sha, 2);
    if (!is_file($path)) {
        return null;
    }

    $raw = @gzuncompress((string) file_get_contents($path));
    if (!is_string($raw)) {
        return null;
    }

    $nullPos = strpos($raw, "\0");
    if ($nullPos === false) {
        return null;
    }

    $header = substr($raw, 0, $nullPos);
    [$type] = explode(' ', $header, 2);

    return [
        'type' => $type,
        'content' => substr($raw, $nullPos + 1),
    ];
}

function avax_gate_git_common_dir(string $gitDir): string
{
    $commonDirFile = $gitDir . '/commondir';
    if (!is_file($commonDirFile)) {
        return $gitDir;
    }

    $commonDir = trim((string) file_get_contents($commonDirFile));
    if (!str_starts_with($commonDir, '/')) {
        $commonDir = $gitDir . '/' . $commonDir;
    }

    $real = realpath($commonDir);

    return is_string($real) ? $real : $commonDir;
}

function avax_gate_read_tree_recursive(string $gitDir, string $treeSha, string $prefix = ''): array
{
    $tree = avax_gate_read_git_object($gitDir, $treeSha);
    if ($tree === null || $tree['type'] !== 'tree') {
        return [];
    }

    $entries = [];
    $data = $tree['content'];
    $offset = 0;
    $length = strlen($data);

    while ($offset < $length) {
        $space = strpos($data, ' ', $offset);
        if ($space === false) {
            break;
        }

        $mode = substr($data, $offset, $space - $offset);
        $offset = $space + 1;

        $null = strpos($data, "\0", $offset);
        if ($null === false) {
            break;
        }

        $name = substr($data, $offset, $null - $offset);
        $offset = $null + 1;
        $sha = bin2hex(substr($data, $offset, 20));
        $offset += 20;

        $path = $prefix . $name;
        if (str_starts_with($mode, '04')) {
            $entries += avax_gate_read_tree_recursive($gitDir, $sha, $path . '/');
            continue;
        }

        $entries[$path] = $sha;
    }

    return $entries;
}

function avax_gate_list_worktree_files(string $root): array
{
    $files = [];
    $skipDirs = [
        '.git' => true,
        'vendor' => true,
        '_pack' => true,
        'node_modules' => true,
        'cache' => true,
        'coverage' => true,
        'tmp' => true,
        'var' => true,
        '.qoder' => true,
    ];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
            static function (SplFileInfo $current) use ($skipDirs): bool {
                if (!$current->isDir()) {
                    return true;
                }

                return !isset($skipDirs[$current->getFilename()]);
            }
        )
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $relative = avax_gate_normalize_path($root, $file->getPathname());
        if (avax_gate_is_ignored_worktree_path($relative)) {
            continue;
        }

        $files[] = $relative;
    }

    sort($files);

    return $files;
}

function avax_gate_is_ignored_worktree_path(string $relative): bool
{
    if ($relative === '.git' || $relative === '.env') {
        return true;
    }

    if (preg_match('#(^|/)(vendor|_pack|node_modules|cache|coverage|tmp|var|\.qoder|\.idea|\.vscode|\.phpunit\.cache)(/|$)#', $relative) === 1) {
        return true;
    }

    if (preg_match('#(^|/)avax\.part-\d+\.txt$#', $relative) === 1) {
        return true;
    }

    if (preg_match('#(^|/)\.phpunit\.result\.cache$#', $relative) === 1) {
        return true;
    }

    return false;
}

function avax_gate_path_matches_changed_file(string $findingPath, string $changedFile): bool
{
    $findingPath = trim($findingPath, '/');
    $changedFile = trim($changedFile, '/');

    if ($findingPath === '' || $changedFile === '') {
        return false;
    }

    if ($findingPath === $changedFile) {
        return true;
    }

    if (str_starts_with($changedFile, $findingPath . '/')) {
        return true;
    }

    return str_starts_with($findingPath, $changedFile . '/');
}

function avax_gate_filter_changed_findings(array $findings, array $changedFiles): array
{
    return array_values(array_filter($findings, static function (array $finding) use ($changedFiles): bool {
        $path = (string) ($finding['path'] ?? $finding['file'] ?? '');
        foreach ($changedFiles as $changedFile) {
            if (avax_gate_path_matches_changed_file($path, $changedFile)) {
                return true;
            }
        }

        return false;
    }));
}

function avax_gate_message_from_finding(array $finding): string
{
    foreach (['message', 'finding', 'why'] as $key) {
        if (isset($finding[$key]) && is_scalar($finding[$key])) {
            return trim((string) $finding[$key]);
        }
    }

    return '';
}

function avax_gate_type_from_message(string $message): string
{
    $lower = strtolower($message);

    $types = [
        'missing_readme' => ['missing readme'],
        'missing_dictionary' => ['missing dictionary'],
        'missing_adr' => ['no adr', 'missing adr'],
        'stale_doc_marker' => ['todo marker', 'draft marker', 'wip marker', 'placeholder marker', 'coming soon'],
        'orphan_doc_reference' => ['references class/interface'],
        'missing_diagram' => ['lacks a corresponding mermaid diagram'],
        'invalid_mermaid' => ['mermaid'],
        'phpstan_type_error' => ['expects', 'return type', 'parameter', 'property'],
        'phpstan_dead_assertion' => ['will always evaluate'],
        'phpstan_missing_symbol' => ['does not exist', 'not found', 'undefined'],
    ];

    foreach ($types as $type => $needles) {
        foreach ($needles as $needle) {
            if (str_contains($lower, $needle)) {
                return $type;
            }
        }
    }

    return 'governance_finding';
}

function avax_gate_baseline_entry(string $tool, array $finding, string $root): array
{
    $file = avax_gate_normalize_path($root, (string) ($finding['path'] ?? $finding['file'] ?? ''));
    $line = isset($finding['line']) && $finding['line'] !== '' ? (int) $finding['line'] : null;
    $severity = (string) ($finding['severity'] ?? 'MEDIUM');
    $message = avax_gate_message_from_finding($finding);
    $type = (string) ($finding['pattern'] ?? $finding['finding_type'] ?? avax_gate_type_from_message($message));

    $identity = [
        'tool' => $tool,
        'file' => $file,
        'line' => $line,
        'severity' => $severity,
        'finding_type' => $type,
        'message' => $message,
    ];

    $createdAt = date('Y-m-d');

    return $identity + [
        'id' => sha1(json_encode($identity, JSON_UNESCAPED_SLASHES)),
        'owner' => 'AvaX governance remediation backlog',
        'reason_for_deferral' => 'Pre-existing legacy debt captured during governance gate adoption; not introduced by the current changed scope.',
        'remediation_category' => avax_gate_remediation_category($tool, $file, $type, $message),
        'created_at' => $createdAt,
        'expires_at' => null,
        'review_after' => date('Y-m-d', strtotime($createdAt . ' +90 days')),
        'legacy_debt_only' => true,
    ];
}

function avax_gate_remediation_category(string $tool, string $file, string $type, string $message): string
{
    if ($tool === 'phpstan') {
        if (str_contains($file, 'components/Identity/')) {
            return 'identity-phpstan-type-safety';
        }

        if (str_contains($message, 'will always evaluate')) {
            return 'test-assertion-type-cleanup';
        }

        return 'phpstan-type-safety';
    }

    if ($tool === 'shallow-tests') {
        if (preg_match('#(?:Identity|Auth|Token|Access|Security|Tenant|Credential|Session)#', $file) === 1) {
            return 'security-sensitive-test-proof';
        }

        return 'behavioral-test-proof';
    }

    if ($tool === 'self-explaining-architecture') {
        if (str_contains($type, 'missing')) {
            return 'missing-self-explaining-documentation';
        }

        return 'documentation-quality';
    }

    return 'governance-remediation';
}

function avax_gate_write_baseline(string $path, string $tool, array $findings, string $root): void
{
    $entries = array_map(
        static fn(array $finding): array => avax_gate_baseline_entry($tool, $finding, $root),
        $findings
    );

    usort($entries, static fn(array $a, array $b): int => [$a['file'], $a['line'] ?? 0, $a['finding_type'], $a['message']] <=> [$b['file'], $b['line'] ?? 0, $b['finding_type'], $b['message']]);

    $baseline = [
        'schema_version' => 1,
        'tool' => $tool,
        'generated_at' => date('Y-m-d H:i:s'),
        'policy' => [
            'full' => 'Fail on every finding.',
            'baseline' => 'Allow only entries recorded in this file; fail on new or stale baseline entries.',
            'changed' => 'Fail on findings in changed files according to the gate policy.',
        ],
        'entry_count' => count($entries),
        'entries' => $entries,
    ];

    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    file_put_contents($path, json_encode($baseline, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
}

function avax_gate_load_baseline(string $path): array
{
    if (!file_exists($path)) {
        return ['error' => "Baseline file missing: {$path}", 'entries' => []];
    }

    $decoded = json_decode((string) file_get_contents($path), true);
    if (!is_array($decoded)) {
        return ['error' => "Baseline file is not valid JSON: {$path}", 'entries' => []];
    }

    $entries = $decoded['entries'] ?? [];
    if (!is_array($entries)) {
        return ['error' => "Baseline file has invalid entries: {$path}", 'entries' => []];
    }

    return ['error' => null, 'entries' => $entries, 'entry_count' => $decoded['entry_count'] ?? count($entries)];
}

function avax_gate_compare_with_baseline(string $tool, array $findings, string $baselinePath, string $root): array
{
    $baseline = avax_gate_load_baseline($baselinePath);
    if ($baseline['error'] !== null) {
        return [
            'valid' => false,
            'error' => $baseline['error'],
            'current_entries' => [],
            'new_entries' => [],
            'stale_entries' => [],
            'baseline_entries' => [],
        ];
    }

    $currentEntries = array_map(
        static fn(array $finding): array => avax_gate_baseline_entry($tool, $finding, $root),
        $findings
    );

    $baselineById = [];
    foreach ($baseline['entries'] as $entry) {
        if (isset($entry['id'])) {
            $baselineById[(string) $entry['id']] = $entry;
        }
    }

    $currentById = [];
    foreach ($currentEntries as $entry) {
        $currentById[(string) $entry['id']] = $entry;
    }

    $newEntries = array_values(array_diff_key($currentById, $baselineById));
    $staleEntries = array_values(array_diff_key($baselineById, $currentById));

    return [
        'valid' => $newEntries === [] && $staleEntries === [],
        'error' => null,
        'current_entries' => array_values($currentById),
        'new_entries' => $newEntries,
        'stale_entries' => $staleEntries,
        'baseline_entries' => array_values($baselineById),
    ];
}

function avax_gate_count_by_severity(array $findings): array
{
    $counts = ['BLOCKER' => 0, 'HIGH' => 0, 'MEDIUM' => 0, 'LOW' => 0, 'INFO' => 0];
    foreach ($findings as $finding) {
        $severity = (string) ($finding['severity'] ?? 'INFO');
        $counts[$severity] = ($counts[$severity] ?? 0) + 1;
    }

    return $counts;
}

function avax_gate_print_baseline_result(string $tool, array $result): void
{
    if ($result['error'] !== null) {
        echo "RED — {$tool} baseline check cannot run: {$result['error']}\n";
        return;
    }

    echo "=== {$tool} Baseline Mode ===\n";
    echo "Baseline entries: " . count($result['baseline_entries']) . "\n";
    echo "Current findings: " . count($result['current_entries']) . "\n";
    echo "New findings: " . count($result['new_entries']) . "\n";
    echo "Stale baseline entries: " . count($result['stale_entries']) . "\n";

    if ($result['valid']) {
        echo "GREEN_WITH_BASELINE — current findings match the recorded legacy baseline.\n";
        return;
    }

    foreach (['new_entries' => 'NEW', 'stale_entries' => 'STALE'] as $key => $label) {
        foreach (array_slice($result[$key], 0, 25) as $entry) {
            echo "[{$label}] {$entry['severity']} {$entry['file']}";
            if ($entry['line'] !== null) {
                echo ":{$entry['line']}";
            }
            echo " {$entry['finding_type']} — {$entry['message']}\n";
        }
    }
}

function avax_gate_print_changed_result(string $tool, array $changedFiles, array $findings, bool $failOnMedium): bool
{
    $blocking = [];
    foreach ($findings as $finding) {
        $severity = (string) ($finding['severity'] ?? 'INFO');
        if ($failOnMedium || in_array($severity, ['BLOCKER', 'HIGH'], true)) {
            $blocking[] = $finding;
        }
    }

    echo "=== {$tool} Changed Mode ===\n";
    echo "Changed files: " . count($changedFiles) . "\n";
    echo "Findings in changed scope: " . count($findings) . "\n";
    echo "Blocking findings: " . count($blocking) . "\n";

    foreach (array_slice($blocking, 0, 25) as $finding) {
        $message = avax_gate_message_from_finding($finding);
        $type = (string) ($finding['pattern'] ?? $finding['finding_type'] ?? avax_gate_type_from_message($message));
        echo "[{$finding['severity']}] {$finding['path']} {$type} — {$message}\n";
    }

    if ($blocking === []) {
        echo "GREEN — changed scope has no blocking findings for this gate.\n";
        return true;
    }

    echo "RED — changed scope has blocking findings.\n";
    return false;
}
