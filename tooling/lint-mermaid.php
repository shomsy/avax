<?php

declare(strict_types=1);

/**
 * Lint script to validate that every how-this-works.md file contains
 * at least one Mermaid code block.
 *
 * Exit codes:
 *   0 - All how-this-works.md files have Mermaid blocks
 *   1 - One or more files are missing Mermaid blocks
 */

const EXIT_SUCCESS    = 0;
const EXIT_FAILURE    = 1;
const TARGET_FILENAME = 'how-this-works.md';

/**
 * Relative paths that should be excluded from validation.
 *
 * @var list<string>
 */
const EXCLUDED_PATH_PREFIXES = [
    '.agents/',
    'Foundation/Auth/docs/',
];

chdir(directory: __DIR__);

echo "=== Mermaid Validation for how-this-works.md ===\n\n";

$files      = findHowThisWorksFiles(rootDirectory: __DIR__);
$totalFiles = count(value: $files);

if ($totalFiles === 0) {
    echo "No how-this-works.md files found.\n";
    exit(EXIT_SUCCESS);
}

echo "Found {$totalFiles} how-this-works.md files\n\n";

$missingFiles = [];

foreach ($files as $filePath) {
    if (containsMermaidBlock(filePath: $filePath)) {
        echo "PASS: {$filePath}\n";

        continue;
    }

    echo "FAIL: {$filePath} - missing Mermaid block\n";
    $missingFiles[] = $filePath;
}

echo "\n=== Summary ===\n";
echo "Total files: {$totalFiles}\n";
echo 'Missing mermaid: ' . count(value: $missingFiles) . "\n";

if ($missingFiles !== []) {
    echo "\nFAIL: " . count(value: $missingFiles) . " how-this-works.md file(s) missing Mermaid diagram block\n";
    exit(EXIT_FAILURE);
}

echo "\nPASS: All how-this-works.md files have Mermaid diagrams\n";
exit(EXIT_SUCCESS);

/**
 * Find all relevant how-this-works.md files under the given root directory.
 *
 * @return list<string>
 */
function findHowThisWorksFiles(string $rootDirectory): array
{
    $files = [];

    $iterator = new RecursiveIteratorIterator(
        iterator: new RecursiveDirectoryIterator(
            directory: $rootDirectory,
            flags: FilesystemIterator::SKIP_DOTS,
        ),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo) {
            continue;
        }

        if (! $file->isFile()) {
            continue;
        }

        if ($file->getFilename() !== TARGET_FILENAME) {
            continue;
        }

        $relativePath = toRelativePath(
            fullPath: $file->getPathname(),
            rootDirectory: $rootDirectory,
        );

        if (shouldSkip(relativePath: $relativePath)) {
            continue;
        }

        $files[] = $relativePath;
    }

    sort(array: $files);

    return $files;
}

function containsMermaidBlock(string $filePath): bool
{
    $content = file_get_contents(filename: $filePath);

    if ($content === false) {
        throw new RuntimeException(message: "Unable to read file: {$filePath}");
    }

    return preg_match(pattern: '/```mermaid\b/i', subject: $content) === 1;
}

function shouldSkip(string $relativePath): bool
{
    return array_any(array: EXCLUDED_PATH_PREFIXES, callback: static fn ($prefix) => str_starts_with(haystack: $relativePath, needle: $prefix));

}

function toRelativePath(string $fullPath, string $rootDirectory): string
{
    $normalizedRoot = rtrim(string: $rootDirectory, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if (str_starts_with(haystack: $fullPath, needle: $normalizedRoot)) {
        return substr(string: $fullPath, offset: strlen(string: $normalizedRoot));
    }

    return $fullPath;
}
