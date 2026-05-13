<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-component-static-state-safety.php
 *
 * Fails if mutable static state exists in active runtime component without:
 * - ResettableState interface
 * - reset() method
 * - boot-only declaration
 * - explicit safe reason
 *
 * Allows constants and immutable static maps.
 */

$scanDirs = [
    __DIR__ . '/../../components',
    __DIR__ . '/../../framework',
];

$knownSafePatterns = [
    '/const\s+\w+/',            // typed constants
    '/private\s+const\s+\w+/',
    '/public\s+const\s+\w+/',
    '/protected\s+const\s+\w+/',
    '/self::\$\w+/',             // self::static access
];

$unsafePatterns = [
    '/static\s+\$\w+\s*=\s*(?!\[)(?!\w+::)(?!\d+)(?!\s*null\s*;)/', // mutable static with assignment
];

$knownResettable = [
    'GlobalEventListenerState',
    'GlobalDatabaseLifecycleState',
    'LazyProxy',
    'Container',
    'WarmStateContract',
];

$violations = [];
$checked    = 0;

foreach ($scanDirs as $scanDir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path    = $file->getPathname();
        $content = file_get_contents($path);
        if ($content === false) {
            continue;
        }

        // Skip files with no static declarations
        if (strpos($content, 'static $') === false && strpos($content, 'self::$') === false) {
            continue;
        }

        $relativePath = str_replace(__DIR__ . '/../../', '', $path);

        // Check if class has reset() or implements resettable
        $hasReset             = preg_match('/function\s+reset\s*\(/', $content);
        $implementsResettable = preg_match('/implements\s+.*ResettableState/', $content);
        $isBootOnly           = preg_match('/boot.*only/i', $content) || preg_match('/BOOT_ONLY/', $content);

        if ($hasReset || $implementsResettable || $isBootOnly) {
            $checked++;
            continue;
        }

        // Check for mutable static assignments
        $lines = explode("\n", $content);
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/static\s+\$\w+\s*=\s*/', $line) && ! preg_match('/const\s+/', $line)) {
                // Check if it's in a known resettable class
                foreach ($knownResettable as $safe) {
                    if (strpos($content, "class $safe") !== false || strpos($content, $safe) !== false) {
                        $checked++;
                        continue 2;
                    }
                }

                $violations[] = "$relativePath:" . ($lineNum + 1) . " — mutable static without reset: " . trim($line);
                $checked++;
            }
        }
    }
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " unsafe static state found:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked static state holders checked, all safe or resettable\n";
exit(0);
