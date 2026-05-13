<?php

declare(strict_types=1);

/**
 * check-empty-production-classes.php
 *
 * Fails on empty concrete production classes unless:
 * - Marker/exception class
 * - Interface/abstract
 * - Test class
 * - ROADMAP/SCAFFOLD classified
 * - Evidence/recovery/labs
 */

$baseDir = dirname(__DIR__);
$failures = [];
$scanned = 0;

$scanDirs = [
    $baseDir . '/framework/System',
    $baseDir . '/components',
];

foreach ($scanDirs as $scanDir) {
    if (!is_dir($scanDir)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanDir));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;

        $path = $file->getPathname();

        // Skip test files
        if (str_contains($path, '/tests/')) continue;

        $content = file_get_contents($path);
        if ($content === false) continue;

        // Check for class definitions
        if (!preg_match('/^\s*(?:final\s+|abstract\s+|readonly\s+)*class\s+(\w+)/m', $content, $m)) continue;

        $className = $m[1];
        $scanned++;

        // Skip exceptions (often empty marker classes)
        if (str_ends_with($className, 'Exception') || str_ends_with($className, 'Error')) continue;

        // Skip interfaces and abstract classes
        if (preg_match('/^\s*(?:final\s+)?(?:readonly\s+)?interface\s+' . $className . '/m', $content)) continue;
        if (preg_match('/^\s*abstract\s+class\s+' . $className . '/m', $content)) continue;

        // Check if class has meaningful content
        // A class is "empty" if it only has constructor and no methods/properties/constants
        $classBody = getClassBody($content, $className);
        if ($classBody === null) continue;

        $hasMethods = preg_match('/^\s*(?:public|private|protected|static|abstract|final)\s+function\s+/m', $classBody);
        $hasProperties = preg_match('/^\s*(?:public|private|protected|readonly)\s+\$/' . 'm', $classBody);
        $hasConstants = preg_match('/^\s*(?:public\s+)?const\s+/m', $classBody);
        $hasAttributes = preg_match('/^\s*#\[/' . 'm', $classBody);
        $hasDocBlock = preg_match('/\/\*\*.*?\*\//s', $classBody);

        if (!$hasMethods && !$hasProperties && !$hasConstants && !$hasAttributes && !$hasDocBlock) {
            // Check if it's in a ROADMAP/SCAFFOLD component
            if (isScaffoldComponent($path)) continue;

            $failures[] = $path;
        }
    }
}

function getClassBody(string $content, string $className): ?string
{
    $pattern = '/(?:final\s+|abstract\s+|readonly\s+)*class\s+' . preg_quote($className, '/') . '\s*(?:extends\s+\S+\s*)?(?:implements\s+(?:[^{]+))?\s*\{/s';
    if (!preg_match($pattern, $content, $m, PREG_OFFSET_CAPTURE)) {
        return null;
    }

    $start = $m[0][1] + strlen($m[0][0]) - 1;
    $braceCount = 1;
    $i = $start + 1;
    $len = strlen($content);
    while ($i < $len && $braceCount > 0) {
        if ($content[$i] === '{') $braceCount++;
        elseif ($content[$i] === '}') $braceCount--;
        $i++;
    }

    return substr($content, $start + 1, $i - $start - 2);
}

function isScaffoldComponent(string $path): bool
{
    $scaffoldIndicators = ['/SCAFFOLD/', '/ROADMAP/', '/EVIDENCE_ONLY/', '/LABS_ONLY/'];
    foreach ($scaffoldIndicators as $indicator) {
        if (str_contains($path, $indicator)) return true;
    }
    return false;
}

if ($failures !== []) {
    echo "FAIL: " . count($failures) . " potentially empty production classes found:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    echo "\nNote: Classes may be marker/value objects. Review individually.\n";
    exit(1);
}

echo "PASS: $scanned production classes scanned, no empty classes found\n";
exit(0);
