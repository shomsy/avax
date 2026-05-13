<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\WarmCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Throwable;

final class WarmCompiledCacheFlow
{
    /** @var array<string, CompiledCacheArtifactDefinition> */
    private array $definitions = [];

    public function __construct(
        private readonly CompiledCacheDirectory $compiledCacheDirectory,
        private readonly CompiledCacheManifest  $compiledCacheManifest,
        private readonly Filesystem             $filesystem,
    ) {
    }

    public static function create(
        CompiledCacheDirectory $compiledCacheDirectory,
        CompiledCacheManifest $compiledCacheManifest,
        Filesystem $filesystem,
    ): self {
        return new self(
            compiledCacheDirectory: $compiledCacheDirectory,
            compiledCacheManifest : $compiledCacheManifest,
            filesystem            : $filesystem,
        );
    }

    public function add(CompiledCacheArtifactDefinition $compiledCacheArtifactDefinition): self
    {
        $this->definitions[$compiledCacheArtifactDefinition->name] = $compiledCacheArtifactDefinition;

        return $this;
    }

    public function warm(): array
    {
        $results = [];

        foreach ($this->definitions as $definition) {
            try {
                $compileFlow = new CompileCache(
                    compiledCacheDirectory: $this->compiledCacheDirectory,
                    compiledCacheManifest : $this->compiledCacheManifest,
                    filesystem            : $this->filesystem,
                );

                $artifact = $compileFlow->compile(
                    name                  : $definition->name,
                    build                 : $definition->builder,
                    compiledCacheSources : $definition->sources,
                );

                $results[$definition->name] = ['success' => true, 'artifact' => $artifact];
            } catch (Throwable $throwable) {
                $results[$definition->name] = ['success' => false, 'error' => $throwable];
            }
        }

        return $results;
    }
}
