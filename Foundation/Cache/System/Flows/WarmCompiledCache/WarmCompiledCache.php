<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\WarmCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Flows\CompileCache\CompileCache;
use Closure;
use Throwable;

final class CompiledCacheArtifactDefinition
{
    public function __construct(
        public string               $name,
        public Closure             $builder,
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
        return new self($directory, $manifest);
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
                    $this->directory,
                    $this->manifest
                );

                $artifact = $compileFlow->compile(
                    $definition->name,
                    $definition->builder,
                    $definition->sources
                );

                $results[$definition->name] = ['success' => true, 'artifact' => $artifact];
            } catch (Throwable $e) {
                $results[$definition->name] = ['success' => false, 'error' => $e];
            }
        }

        return $results;
    }
}