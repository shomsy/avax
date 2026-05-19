<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Normalizes tooling folder names and namespaces.
 */
final class NormalizeTooling
{
    private string $root;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function execute(): void
    {
        echo "=== Normalizing Tooling ===\n\n";

        $renames = [
            'tooling/architecture' => 'tooling/Architecture',
            'tooling/docs' => 'tooling/Docs',
            'tooling/pre-commit' => 'tooling/PreCommit',
            'tooling/quality' => 'tooling/Quality',
            'tooling/refactor' => 'tooling/Refactor',
        ];

        foreach ($renames as $old => $new) {
            $oldPath = $this->root.'/'.$old;
            $newPath = $this->root.'/'.$new;

            if (is_dir($oldPath) && $old !== $new) {
                // On case-insensitive filesystems, we might need a temp name
                $tmpPath = $newPath.'_tmp';
                rename($oldPath, $tmpPath);
                rename($tmpPath, $newPath);
                echo "  [RENAMED DIR] $old -> $new\n";
            }
        }

        // Now update namespaces in all tooling files
        $this->updateNamespaces($this->root.'/tooling');
    }

    private function updateNamespaces(string $path): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $relativePath = str_replace($this->root.'/tooling/', '', $file->getPathname());
            $parts = explode('/', $relativePath);
            array_pop($parts); // remove filename

            $subNamespace = implode('\\', array_map(fn ($p) => str_replace('-', '', ucfirst($p)), $parts));
            $expectedNs = 'Avax\Tooling'.($subNamespace ? '\\'.$subNamespace : '');

            if (preg_match('/^namespace\s+([A-Za-z0-9_\\\\]+);/m', $content, $matches)) {
                $currentNs = $matches[1];
                if ($currentNs !== $expectedNs) {
                    $newContent = preg_replace('/^namespace\s+[A-Za-z0-9_\\\\]+;/m', "namespace $expectedNs;", $content);
                    file_put_contents($file->getPathname(), $newContent);
                    echo '  [UPDATED NS] '.$relativePath." ($expectedNs)\n";
                }
            }
        }
    }
}

$root = dirname(__DIR__, 2);
$normalizer = new NormalizeTooling($root);
$normalizer->execute();
echo "\nDone!\n";
