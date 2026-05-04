<?php

$auditOutput = shell_exec('php tooling/audit_broken_refs.php 2>&1');
$lines = explode("\n", $auditOutput);

$missingRefs = [];
$currentRef = null;

foreach ($lines as $line) {
    if (preg_match('/^MISSING:\s+(.*?)\s+\[(.*?)\]/', $line, $matches)) {
        $currentRef = $matches[1];
        $missingRefs[$currentRef] = [
            'severity' => $matches[2],
            'usages' => [],
        ];
    } elseif ($currentRef && preg_match('/^\s+-\s+(.*?):(\d+)\s+\((.*?)\)/', $line, $matches)) {
        $missingRefs[$currentRef]['usages'][] = [
            'file' => $matches[1],
            'line' => $matches[2],
            'type' => $matches[3],
        ];
    }
}

$categories = [
    'test-only' => [],
    'docs-only' => [],
    'vendor-external' => [],
    'stale-namespace' => [],
    'real-production' => [],
];

foreach ($missingRefs as $ref => $data) {
    $usages = $data['usages'];
    $isTestOnly = true;
    $isDocOnly = true;

    foreach ($usages as $usage) {
        if (!str_contains($usage['file'], '/tests/')) {
            $isTestOnly = false;
        }
        if (!str_contains($usage['type'], 'docblock') && !str_contains($usage['type'], 'comment')) {
            $isDocOnly = false;
        }
    }

    if ($isTestOnly) {
        $categories['test-only'][$ref] = $data;
    } elseif ($isDocOnly) {
        $categories['docs-only'][$ref] = $data;
    } elseif (str_starts_with($ref, 'Psr\\') || str_starts_with($ref, 'Symfony\\') || str_starts_with($ref, 'Illuminate\\')) {
        $categories['vendor-external'][$ref] = $data;
    } elseif (str_contains($ref, 'Avax\\Components\\') && count($usages) > 0) {
        $categories['stale-namespace'][$ref] = $data;
    } else {
        $categories['real-production'][$ref] = $data;
    }
}

$markdown = "# Broken Reference Groups\n\n";
foreach ($categories as $cat => $refs) {
    $markdown .= "## " . strtoupper($cat) . " (" . count($refs) . ")\n\n";
    foreach ($refs as $ref => $data) {
        $markdown .= "- **$ref** [{$data['severity']}]\n";
        foreach ($data['usages'] as $usage) {
            // Strip absolute path prefix for brevity
            $relFile = str_replace(getcwd() . '/', '', $usage['file']);
            $markdown .= "  - {$relFile}:{$usage['line']} ({$usage['type']})\n";
        }
    }
    $markdown .= "\n";
}

file_put_contents('Code-Review-And-ToDo/v1-integrity/broken-reference-groups.md', $markdown);
echo "Categorization complete. Output written to Code-Review-And-ToDo/v1-integrity/broken-reference-groups.md\n";
