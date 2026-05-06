<?php

declare(strict_types=1);

if ($argc < 2) {
    fwrite(STDERR, "Usage: php {$argv[0]} <file1> [file2...]\n");
    exit(1);
}

$files = array_slice($argv, 1);
$fixed = 0;

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        fwrite(STDERR, "Error reading: $file\n");

        continue;
    }

    $count = count($lines);
    $garbageStart = $count; // default: no garbage

    // Scan from bottom upwards
    $i = $count - 1;
    // Skip trailing blank lines
    while ($i >= 0 && trim($lines[$i]) === '') {
        $i--;
    }
    // Now skip lines that are only a closing brace (after trim)
    while ($i >= 0 && trim($lines[$i]) === '}') {
        $i--;
    }
    // If there are remaining lines, any remaining non-blank line before is the last content line.
    // The lines after that index (i+1 ...) are considered garbage if they include non-} non-blank content.
    // But we need to check if we stopped because we found a non-blank non-} line.
    if ($i >= 0 && trim($lines[$i]) !== '' && trim($lines[$i]) !== '}') {
        // This line is garbage start. Everything after this (including this line) should be removed.
        $garbageStart = $i + 1;
    } else {
        // No garbage detected (either only whitespace/} at end)
        $garbageStart = $count;
    }

    if ($garbageStart < $count) {
        // Truncate array and write back
        $newLines = array_slice($lines, 0, $garbageStart);
        // Ensure file ends with newline
        $newContent = implode("\n", $newLines)."\n";
        file_put_contents($file, $newContent);
        echo "Fixed trailing garbage in $file (removed ".($count - $garbageStart)." lines)\n";
        $fixed++;
    }
}

echo "Fixed $fixed files.\n";
