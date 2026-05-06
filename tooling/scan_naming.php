<?php

declare(strict_types=1);

$directories = ['components', 'framework'];
$forbiddenWords = [
    'manager', 'service', 'helper', 'util', 'common', 'shared', 'core', 'support',
];
$suspiciousSuffixes = [
    'interface.php', 'abstract.php', 'base.php', 'trait.php',
];

$violations = [];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $name = strtolower($file->getFilename());
            if ($name === '.' || $name === '..') {
                continue;
            }

            foreach ($forbiddenWords as $word) {
                if (str_contains($name, $word)) {
                    $violations['folders'][] = "[FORBIDDEN WORD: $word] ".$file->getPathname();
                }
            }
        } else {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $name = strtolower($file->getFilename());

            // Check forbidden words
            foreach ($forbiddenWords as $word) {
                if (str_contains($name, $word)) {
                    $violations['files_forbidden'][] = "[FORBIDDEN WORD: $word] ".$file->getPathname();
                }
            }

            // Check suffixes
            foreach ($suspiciousSuffixes as $suffix) {
                if (str_ends_with($name, $suffix)) {
                    $violations['files_suffix'][] = "[SUSPICIOUS SUFFIX: $suffix] ".$file->getPathname();
                }
            }
        }
    }
}

echo "=== BRUTAL NAMING CONVENTION AUDIT ===\n\n";

if (! empty($violations['folders'])) {
    echo "🚨 FORBIDDEN FOLDERS:\n";
    foreach (array_unique($violations['folders']) as $v) {
        echo "- $v\n";
    }
    echo "\n";
}

if (! empty($violations['files_forbidden'])) {
    echo "🚨 FORBIDDEN FILE NAMES:\n";
    foreach (array_unique($violations['files_forbidden']) as $v) {
        echo "- $v\n";
    }
    echo "\n";
}

if (! empty($violations['files_suffix'])) {
    echo "⚠️ SUSPICIOUS SUFFIXES (Technical details leaking into names):\n";
    foreach (array_unique($violations['files_suffix']) as $v) {
        echo "- $v\n";
    }
    echo "\n";
}

if (empty($violations)) {
    echo "✨ PERFECT! No naming violations found!\n";
} else {
    echo 'Total Folder Violations: '.count(array_unique($violations['folders'] ?? []))."\n";
    echo 'Total File Violations: '.count(array_unique($violations['files_forbidden'] ?? []))."\n";
    echo 'Total Suffix Warnings: '.count(array_unique($violations['files_suffix'] ?? []))."\n";
}
