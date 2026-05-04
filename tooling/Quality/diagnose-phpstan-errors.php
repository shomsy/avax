#!/usr/bin/env php
<?php

declare(strict_types=1);

$output = shell_exec('vendor/bin/phpstan analyse --memory-limit=1G --no-progress -l 5 2>&1');
$lines = explode("\n", $output ?? '');

$errors = [];
$currentFile = '';

foreach ($lines as $line) {
    if (preg_match('/^  Line\s+(.+)$/', $line, $m)) {
        $currentFile = $m[1];
    }
    if (preg_match('/^\s+(\d+)\s+(.+?)(?:\s+🪪\s+(.+))?$/', $line, $m)) {
        $type = trim($m[2]);
        $identifier = isset($m[3]) ? trim($m[3]) : '';
        if ($currentFile && (strpos($identifier, 'non-ignorable') !== false || strpos($type, 'non-ignorable') !== false)) {
            $errors[] = [
                    'file' => $currentFile,
                    'line' => $m[1],
                    'type' => $type,
                    'id' => $identifier,
            ];
        }
    }
}

$byType = [];
foreach ($errors as $e) {
    $key = preg_replace('/\(.*\)/', '', $e['id']);
    $key = trim($key);
    $byType[$key][] = $e;
}

echo "=== PHPStan Non-Baselineable Errors ===\n\n";
foreach ($byType as $type => $list) {
    echo "{$type}: " . count($list) . " errors\n";
}

echo "\n=== Quick Fix Candidates ===\n\n";
foreach ($byType as $type => $list) {
    if (strpos($type, 'duplicateProperty') !== false) {
        echo "DUPLICATE PROPERTY: " . count($list) . " files\n";
        foreach ($list as $e) {
            echo "  - {$e['file']}#{$e['line']}\n";
        }
        echo "\n";
    }
    if (strpos($type, 'class.notFound') !== false) {
        echo "CLASS NOT FOUND: " . count($list) . " files\n";
        foreach (array_slice($list, 0, 10) as $e) {
            echo "  - {$e['file']}#{$e['line']}\n";
        }
        if (count($list) > 10) echo "  ... and " . (count($list) - 10) . " more\n";
        echo "\n";
    }
}