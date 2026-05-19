<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Detects 'use' statements that point to a namespace (folder) instead of a class.
 * Auto-fixes them by appending the class name if it exists in that folder.
 */
final class FixBrokenImports
{
    private string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function execute(): void
    {
        echo "=== Fixing Broken Imports ===\n\n";

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->root, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            if (str_contains($file->getPathname(), '/vendor/')) {
                continue;
            }

            $this->processFile($file->getPathname());
        }
    }

    private function processFile(string $path): void
    {
        $content = file_get_contents($path);
        if (! $content) {
            return;
        }

        $newContent = preg_replace_callback(
            '/^use\s+([A-Za-z0-9_\\\\]+);/m',
            function ($matches) {
                $useLine = $matches[0];
                $fullClass = $matches[1];

                // If it looks like a global class, skip
                if (! str_contains($fullClass, '\\')) {
                    return $useLine;
                }

                // Check if this class exists
                if ($this->isClassInPsr4($fullClass)) {
                    return $useLine;
                }

                // If not, maybe it's a namespace and the class is inside with the same name
                $parts = explode('\\', $fullClass);
                $className = end($parts);
                $potentialClass = $fullClass.'\\'.$className;

                if ($this->isClassInPsr4($potentialClass)) {
                    echo "  [FIXED] Found moved class: $fullClass -> $potentialClass\n";

                    return "use $potentialClass;";
                }

                return $useLine;
            },
            $content
        );

        if ($newContent !== $content) {
            file_put_contents($path, $newContent);
            echo '  [UPDATED] '.str_replace($this->root.'/', '', $path)."\n";
        }
    }

    private function isClassInPsr4(string $fullClass): bool
    {
        $psr4Map = [
            'Avax\Framework\\' => 'framework/',
            'Avax\Components\\' => 'components/',
            'Avax\Labs\\' => 'labs/',
            'Avax\Benchmarks\\' => 'benchmarks/',
            'Avax\Docs\\' => 'docs/',
            'Avax\Tooling\\' => 'tooling/',
            'Avax\Tests\\' => 'tests/',
        ];

        foreach ($psr4Map as $prefix => $dir) {
            if (str_starts_with($fullClass, $prefix)) {
                $relative = str_replace($prefix, '', $fullClass);
                $path = $this->root.'/'.$dir.str_replace('\\', '/', $relative).'.php';
                if (file_exists($path)) {
                    return true;
                }
            }
        }

        return false;
    }
}

$root = dirname(__DIR__, 2);
$fixer = new FixBrokenImports($root);
$fixer->execute();
echo "\nDone!\n";
