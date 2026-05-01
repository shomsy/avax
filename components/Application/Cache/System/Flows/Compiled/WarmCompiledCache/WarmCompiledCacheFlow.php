<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\WarmCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Throwable;

final class WarmCompiledCacheFlow
{
    /** @var array<string, CompiledCacheArtifactDefinition> */
    private array $definitions = [];

    private readonly CompiledCacheDirectory $compiledCacheDirectory;

    private readonly CompiledCacheManifest $compiledCacheManifest;

    public function __construct(
        CompiledCacheDirectory $directory,
        CompiledCacheManifest  $manifest,
    )
    {
        $this->compiledCacheDirectory = $directory;
        $this->compiledCacheManifest  = $manifest;
    }

    public static function create(
        CompiledCacheDirectory $compiledCacheDirectory,
        CompiledCacheManifest $compiledCacheManifest,
    ) : self
    {
        return new self(directory: $compiledCacheDirectory, manifest: $compiledCacheManifest);
    }

    public function add(CompiledCacheArtifactDefinition $compiledCacheArtifactDefinition) : self
    {
        $this->definitions[$compiledCacheArtifactDefinition->name] = $compiledCacheArtifactDefinition;

        return $this;
    }

    public function warm() : array
    {
        $results = [];

        foreach ($this->definitions as $definition) {
            try {
                $compileFlow = new CompileCache(
                    directory: $this->compiledCacheDirectory,
                    manifest : $this->compiledCacheManifest,
                );

                $artifact = $compileFlow->compile(
                    name   : $definition->name,
                    build  : $definition->builder,
                    sources: $definition->sources,
                );

                $results[$definition->name] = ['success' => true, 'artifact' => $artifact];
            } catch (Throwable $throwable) {
                $results[$definition->name] = ['success' => false, 'error' => $throwable];
            }
        }

        return $results;
    }
}
