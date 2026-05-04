<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Robust PSR-4 Namespace Fixer.
 * Matches directory structure to namespace exactly.
 */
final class EnsurePsr4Namespaces
{
    private array $psr4Map
        = [
            'Avax\Framework\\' => 'framework/',
            'Avax\Components\\' => 'components/',
            'Avax\Labs\\' => 'labs/',
            'Avax\Benchmarks\\' => 'benchmarks/',
            'Avax\Docs\\' => 'docs/',
            'Avax\Tooling\\' => 'tooling/',
            'Avax\Tests\\' => 'tests/',
        ];

    private string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function execute(): void
    {
        echo "=== Ensuring PSR-4 Namespaces (Aggressive) ===\n\n";

        foreach ($this->psr4Map as $prefix => $dir) {
            $path = $this->root . '/' . $dir;
            if (!is_dir($path)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') continue;
                $this->processFile($file->getPathname(), $prefix, $dir);
            }
        }
    }

    private function processFile(string $path, string $prefix, string $baseDir): void
    {
        $content = file_get_contents($path);
        if (!$content) return;

        $relativePath = str_replace($this->root . '/' . $baseDir, '', $path);
        $parts = explode('/', trim($relativePath, '/'));
        array_pop($parts); // remove filename

        // Map directory names to valid namespace parts (remove -)
        $parts = array_map(fn($p) => str_replace('-', '', $p), $parts);

        $expectedNs = rtrim($prefix . implode('\\', $parts), '\\');

        $modified = false;
        if (preg_match('/^namespace\s+([^;]+);/m', $content, $matches)) {
            $currentNs = trim($matches[1]);
            if ($currentNs !== $expectedNs) {
                $content = preg_replace('/^namespace\s+[^;]+;/m', "namespace $expectedNs;", $content);
                $modified = true;
            }
        } else {
            // Missing namespace
            if (preg_match('/^<\?php\s+(declare\(strict_types=1\);)?/s', $content, $m)) {
                $header = $m[0];
                $rest = substr($content, strlen($header));
                $content = $header . "\n\nnamespace $expectedNs;\n" . ltrim($rest);
                $modified = true;
            }
        }

        if ($modified) {
            file_put_contents($path, $content);
            echo "  [FIXED] " . str_replace($this->root . '/', '', $path) . " -> $expectedNs\n";
        }
    }
}

$root = dirname(__DIR__, 2);
$fixer = new EnsurePsr4Namespaces($root);
$fixer->execute();
echo "\nDone!\n";
