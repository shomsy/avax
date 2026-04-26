<?php

declare(strict_types=1);

namespace components\Cache\System\Flows\Compiled\WarmCompiledCache;

use Closure;
use components\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use components\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use components\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use components\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Throwable;

final class CompiledCacheArtifactDefinition
{
    public function __construct(
        public string               $name,
        public Closure              $builder,
        public CompiledCacheSources $sources
    ) {}
}

final class WarmCompiledCacheFlow
{
    /** @var array<string, CompiledCacheArtifactDefinition> */
    private array $definitions = [];

    public function __construct(
        private CompiledCacheDirectory $directory,
        private CompiledCacheManifest  $manifest
    ) {}

    public static function create(
        CompiledCacheDirectory $directory,
        CompiledCacheManifest  $manifest
    ) : self
    {
        return new self(directory: $directory, manifest: $manifest);
    }

    public function add(CompiledCacheArtifactDefinition $definition) : self
    {
        $this->definitions[$definition->name] = $definition;

        return $this;
    }

    public function warm() : array
    {
        $results = [];

        foreach ($this->definitions as $definition) {
            try {
                $compileFlow = new CompileCache(
                    directory: $this->directory,
                    manifest : $this->manifest
                );

                $artifact = $compileFlow->compile(
                    name   : $definition->name,
                    build  : $definition->builder,
                    sources: $definition->sources
                );

                $results[$definition->name] = ['success' => true, 'artifact' => $artifact];
            } catch (Throwable $e) {
                $results[$definition->name] = ['success' => false, 'error' => $e];
            }
        }

        return $results;
    }
}