<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\MetadataWarmup;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CompileClassAttributes;
use Closure;
use Throwable;

/**
 * RegisterMetadataWarmCommands — provides CLI command closures for metadata:warm and metadata:clear.
 *
 * Pre-compiles attribute metadata to disk so hot paths can load compiled metadata
 * instead of using reflection.
 */
final readonly class RegisterMetadataWarmCommands
{
    /**
     * @param list<class-string> $classes
     */
    public function __construct(
        private string          $cacheDir = '',
        private string          $configHash = 'default',
        private array           $classes = [],
        private Filesystem|null $filesystem = null,
    ) {}

    /**
     * @return array<string, Closure>
     */
    public function __invoke() : array
    {
        return [
            'metadata:warm'  => $this->metadataWarmCommand(),
            'metadata:clear' => $this->metadataClearCommand(),
        ];
    }

    private function metadataWarmCommand() : Closure
    {
        $cacheDir   = $this->cacheDir;
        $configHash = $this->configHash;
        $classes    = $this->classes;
        $filesystem = $this->filesystem;

        return static function (array $args) use ($cacheDir, $configHash, $classes, $filesystem) : string {
            $output = "\033[33mMetadata Warm\033[0m\n\n";

            $resolvedCacheDir = $cacheDir !== '' ? $cacheDir : sys_get_temp_dir() . '/avax-metadata-cache';
            $fs               = $filesystem ?? new Filesystem();

            $compiler = new CompileClassAttributes($resolvedCacheDir, $configHash, $fs);

            if ($classes === []) {
                $output .= "No classes configured for metadata warmup.\n";
                $output .= "Pass entity/DTO class names when creating this command.\n";

                return $output;
            }

            $output .= "Compiling attributes for " . count($classes) . " classes...\n\n";

            $compiledCount = 0;
            $errorCount    = 0;

            foreach ($classes as $class) {
                try {
                    $compiler->compile($class);
                    $compiledCount++;
                    $output .= "  \033[32m✓\033[0m {$class}\n";
                } catch (Throwable $e) {
                    $errorCount++;
                    $output .= "  \033[31m✗\033[0m {$class}: " . $e->getMessage() . "\n";
                }
            }

            $output .= "\nCompiled: {$compiledCount}, Errors: {$errorCount}\n";
            $output .= "Cache directory: {$resolvedCacheDir}/compiled-attributes/\n";

            if ($errorCount === 0 && $compiledCount > 0) {
                $output .= "\n\033[32mMetadata warmup complete.\033[0m\n";
            } elseif ($errorCount > 0) {
                $output .= "\n\033[33mMetadata warmup completed with errors.\033[0m\n";
            }

            return $output;
        };
    }

    private function metadataClearCommand() : Closure
    {
        $cacheDir   = $this->cacheDir;
        $filesystem = $this->filesystem;

        return static function (array $args) use ($cacheDir, $filesystem) : string {
            $output = "\033[33mMetadata Clear\033[0m\n\n";

            $resolvedCacheDir = $cacheDir !== '' ? $cacheDir : sys_get_temp_dir() . '/avax-metadata-cache';
            $compiledDir      = $resolvedCacheDir . '/compiled-attributes';
            $fs               = $filesystem ?? new Filesystem();

            if (! $fs->isDirectory($compiledDir)) {
                $output .= "No metadata cache found at {$compiledDir}\n";

                return $output;
            }

            $removed = 0;

            // Remove all files in the compiled-attributes directory
            $items = $fs->listFilesByPattern($compiledDir . '/*');
            foreach ($items as $itemPath) {
                if ($fs->isFile($itemPath)) {
                    $fs->delete($itemPath);
                    $removed++;
                }
            }

            // Remove quarantine subdirectory if it exists
            $quarantineDir = $compiledDir . '/quarantine';
            if ($fs->isDirectory($quarantineDir)) {
                $qItems = $fs->listFilesByPattern($quarantineDir . '/*');
                foreach ($qItems as $qPath) {
                    if ($fs->isFile($qPath)) {
                        $fs->delete($qPath);
                        $removed++;
                    }
                }
                $fs->deleteDirectory($quarantineDir);
            }

            $output .= "Removed {$removed} compiled metadata files\n";
            $output .= "\n\033[32mMetadata cache cleared.\033[0m\n";

            return $output;
        };
    }
}
