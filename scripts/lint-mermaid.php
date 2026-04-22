<?php
/**
 * Lint script to validate how-this-works.md files contain mermaid diagrams
 *
 * Exit codes:
 *   0 - All how-this-works.md files have mermaid blocks
 *   1 - One or more files missing mermaid blocks
 */

chdir(__DIR__);

echo "=== Mermaid Validation for how-this-works.md ===\n\n";

$allFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__)
);

foreach ($iterator as $file) {
    if ($file->getFilename() === 'how-this-works.md') {
        $path = $file->getPathname();
        $relPath = ltrim($path, __DIR__ . '/');
        if (strpos($relPath, '.agents') === false && strpos($relPath, 'Foundation/Auth/docs') !== 0) {
            $allFiles[] = $relPath;
        }
    }
}

sort($allFiles);
$totalFiles = count($allFiles);

if ($totalFiles === 0) {
    echo "No how-this-works.md files found.\n";
    exit 0;
}

echo "Found $totalFiles how-this-works.md files\n\n";

$missingCount = 0;

foreach ($allFiles as $filePath) {
    $content = file_get_contents($filePath);
    if (strpos($content, '```mermaid') !== false) {
        echo "PASS: " . basename($filePath) . "\n";
    } else {
        echo "FAIL: " . basename($filePath) . " - MISSING mermaid block\n";
        $missingCount = $missingCount + 1;
    }
}

echo "\n=== Summary ===\n";
echo "Total files: $totalFiles\n";
echo "Missing mermaid: $missingCount\n";

if ($missingCount > 0) {
    echo "\nFAIL: $missingCount how-this-works.md file(s) missing mermaid diagram block\n";
    exit 1;
}

echo "\nPASS: All how-this-works.md files have mermaid diagrams\n";
exit 0;