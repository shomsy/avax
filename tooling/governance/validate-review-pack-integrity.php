<?php

declare(strict_types=1);

/**
 * Independent review pack integrity validator.
 *
 * Validates generated review-pack folders by reading actual ZIP contents.
 */

function usage(): string
{
    return "Usage: php tooling/governance/validate-review-pack-integrity.php _pack/<run-id>\n";
}

if (($argv[1] ?? '') === '--help' || ($argv[1] ?? '') === '-h') {
    echo usage();
    exit(0);
}

$folder = $argv[1] ?? null;
if ($folder === null) {
    fwrite(STDERR, usage());
    exit(1);
}

$folder = realpath(rtrim($folder, '/')) ?: rtrim($folder, '/');
if (!is_dir($folder)) {
    fwrite(STDERR, "Pack folder not found: {$folder}\n");
    exit(1);
}

function metadata_fragment_findings(string $content): array
{
    $patterns = [
        implode('', ['"', ' . ', 'date(']) => 'unevaluated date concatenation',
        implode('', ['"', ' . ', '(']) => 'unevaluated concatenated expression',
        implode('', [' ', '.', ' ', '(']) => 'unevaluated expression fragment',
        '<?php' => 'PHP source fragment in generated metadata',
        '$totalFiles' => 'unevaluated total-files template variable',
        '$generatedAt' => 'unevaluated generated-at template variable',
    ];

    $findings = [];
    foreach ($patterns as $needle => $reason) {
        if (str_contains($content, $needle)) {
            $findings[] = "{$reason} ({$needle})";
        }
    }

    return $findings;
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

    return (bool) preg_match('/(?:private[_-]?key|credentials|secret)\.(?:pem|key|json|env|txt)$/i', $normalized);
}

function extract_stats_value(string $stats, string $label): ?string
{
    if (preg_match('/^\*\*' . preg_quote($label, '/') . ':\*\*\s*(.+)$/m', $stats, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

$errors = [];
foreach (['README.md', 'MANIFEST.md', 'manifest.json'] as $required) {
    if (!is_file($folder . '/' . $required)) {
        $errors[] = "Missing root metadata file: {$required}";
    }
}

if ($errors === []) {
    $manifestContent = (string) file_get_contents($folder . '/manifest.json');
    $manifest = json_decode($manifestContent, true);
    if (!is_array($manifest)) {
        $errors[] = 'manifest.json is not valid JSON: ' . json_last_error_msg();
    }
} else {
    $manifest = [];
}

if ($errors === []) {
    foreach (['generated_at', 'purpose', 'run_id', 'packs', 'validation_status'] as $field) {
        if (!array_key_exists($field, $manifest)) {
            $errors[] = "manifest.json missing field: {$field}";
        }
    }
}

if ($errors === []) {
    foreach (['README.md', 'MANIFEST.md', 'manifest.json'] as $metadataFile) {
        $content = (string) file_get_contents($folder . '/' . $metadataFile);
        foreach (metadata_fragment_findings($content) as $finding) {
            $errors[] = "{$metadataFile}: {$finding}";
        }
    }

    foreach (['README.md', 'MANIFEST.md'] as $metadataFile) {
        $content = (string) file_get_contents($folder . '/' . $metadataFile);
        foreach ([
            'Generated' => $manifest['generated_at'],
            'Purpose' => $manifest['purpose'],
            'Run ID' => $manifest['run_id'],
            'Validation Status' => $manifest['validation_status'],
        ] as $label => $expected) {
            $line = "{$label}: {$expected}";
            if (!str_contains($content, $line)) {
                $errors[] = "{$metadataFile} missing '{$line}'";
            }
        }
    }
}

if ($errors === []) {
    $runId = basename($folder);
    if ($runId !== $manifest['run_id']) {
        $errors[] = "Folder/run_id mismatch: folder '{$runId}', manifest '{$manifest['run_id']}'";
    }

    foreach ($manifest['packs'] as $pack) {
        if (!is_array($pack) || !isset($pack['name'], $pack['zip_entries'])) {
            $errors[] = 'Invalid pack entry in manifest.json';
            continue;
        }

        $zipPath = $folder . '/' . $pack['name'];
        $zipRealPath = realpath($zipPath) ?: $zipPath;
        if (!is_file($zipPath)) {
            $errors[] = "Missing ZIP listed in manifest: {$pack['name']}";
            continue;
        }

        if (dirname($zipPath) !== $folder) {
            $errors[] = "ZIP path outside current run folder: {$zipPath}";
            continue;
        }

        try {
            $zip = new PharData($zipRealPath);
            $entries = [];
            $statsContent = null;

            foreach (new RecursiveIteratorIterator($zip) as $file) {
                $relativePath = str_replace('phar://' . $zipRealPath . '/', '', $file->getPathname());
                $entries[] = $relativePath;

                if (is_forbidden_pack_path($relativePath)) {
                    $errors[] = "{$pack['name']}: forbidden entry {$relativePath}";
                }

                if (in_array(basename($relativePath), ['REVIEW_CONTEXT.md', 'TREE.txt', 'STATS.md'], true)) {
                    $content = (string) file_get_contents($file->getPathname());
                    foreach (metadata_fragment_findings($content) as $finding) {
                        $errors[] = "{$pack['name']}: {$relativePath}: {$finding}";
                    }
                    if ($relativePath === 'STATS.md') {
                        $statsContent = $content;
                    }
                }
            }
        } catch (Throwable $e) {
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
            'Generated' => $manifest['generated_at'],
            'Purpose' => $manifest['purpose'],
            'Run ID' => $manifest['run_id'],
            'ZIP Entries' => (string) $expectedEntries,
        ] as $label => $expected) {
            $actual = extract_stats_value($statsContent, $label);
            if ($actual !== $expected) {
                $errors[] = "{$pack['name']}: STATS.md {$label} mismatch expected '{$expected}', actual '" . ($actual ?? 'MISSING') . "'";
            }
        }
    }
}

if ($errors !== []) {
    echo "RED: Review pack integrity failed.\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "GREEN: Review pack integrity validated.\n";
echo "Folder: {$folder}\n";
echo "Run ID: {$manifest['run_id']}\n";
echo "Generated: {$manifest['generated_at']}\n";
echo "Purpose: {$manifest['purpose']}\n";
echo "Packs: " . count($manifest['packs']) . "\n";
