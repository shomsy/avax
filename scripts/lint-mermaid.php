<?php
/**
 * Lint script to validate how-this-works.md files contain mermaid diagrams
 *
 * Exit codes:
 *   0 - All how-this-works.md files have mermaid blocks
 *   1 - One or more files missing mermaid blocks
 */

chdir(directory: __DIR__);

echo "=== Mermaid Validation for how-this-works.md ===\n\n";

$allFiles = [];
$iterator = new RecursiveIteratorIterator(
    iterator: new RecursiveDirectoryIterator(directory: __DIR__)
);

foreach ($iterator as $file) {
    if ($file->getFilename() === 'how-this-works.md') {
        $path = $file->getPathname();
        $relPath = ltrim(string: $path, characters: __DIR__ . '/');
        if (! str_contains(haystack: $relPath, needle: '.agents') && ! str_starts_with(haystack: $relPath, needle: 'Foundation/Auth/docs')) {
            $allFiles[] = $relPath;
        }
    }
}

sort(array: $allFiles);
$totalFiles = count(value: $allFiles);

if ($totalFiles === 0) {
    echo "No how-this-works.md files found.\n";
    exit 0;
}

echo "Found {$totalFiles} how-this-works.md files\n\n";

$missingCount = 0;

foreach ($allFiles as $filePath) {
    $content = file_get_contents(filename: $filePath);
    if (str_contains(haystack: $content, needle: '```mermaid')) {
        echo "PASS: " . basename(path: $filePath) . "\n";
    } else {
        echo "FAIL: " . basename(path: $filePath) . " - MISSING mermaid block\n";
        $missingCount = $missingCount + 1;
    }
}

echo "\n=== Summary ===\n";
echo "Total files: {$totalFiles}\n";
echo "Missing mermaid: {$missingCount}\n";

if ($missingCount > 0) {
    echo "\nFAIL: {$missingCount} how-this-works.md file(s) missing mermaid diagram block\n";
    exit 1;
}

echo "\nPASS: All how-this-works.md files have mermaid diagrams\n";
exit 0;