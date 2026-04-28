<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Flows\Compiled\ClearCompiledCache\ClearCompiledCache;
use Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Avax\Components\Application\Cache\System\Flows\Compiled\ReadCompiledCache\ReadCompiledCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final class BuildCompiledCache
{
    private Clock $clock;

    public function __construct(Clock $clock = new SystemClock())
    {
        $this->clock = $clock;
    }

    public function inDirectory(string $directory) : CompiledCacheContract
    {
        return $this->fromConfiguration(
            configuration: CompiledCacheConfiguration::inDirectory(directory: $directory)
        );
    }

    public function fromConfiguration(CompiledCacheConfiguration $configuration) : CompiledCacheContract
    {
        return new class(
            $configuration->directory,
            $configuration,
            $this->clock
        ) implements CompiledCacheContract {
            public function __construct(
                private string                     $directory,
                private CompiledCacheConfiguration $config,
                private Clock                      $clock
            ) {}

            public function read(string $name, callable $build, CompiledCacheSources $sources) : mixed
            {
                $directory = new CompiledCacheDirectory(path: $this->directory);
                $manifest  = CompiledCacheManifest::load(path: $directory->resolveManifestPath()->toString());

                $flow = new ReadCompiledCache(
                    directory: $directory,
                    manifest : $manifest,
                    clock    : $this->clock
                );

                return $flow->read(name: $name, build: $build, sources: $sources);
            }

            public function compile(string $name, callable $build, CompiledCacheSources $sources) : CompiledCacheArtifact
            {
                $directory = new CompiledCacheDirectory(path: $this->directory);
                $manifest  = CompiledCacheManifest::load(path: $directory->resolveManifestPath()->toString());

                $flow = new CompileCache(
                    directory: $directory,
                    manifest : $manifest,
                    clock    : $this->clock
                );

                return $flow->compile(name: $name, build: $build, sources: $sources);
            }

            public function clear(string $name) : void
            {
                $directory = new CompiledCacheDirectory(path: $this->directory);
                $manifest  = CompiledCacheManifest::load(path: $directory->resolveManifestPath()->toString());

                $flow = new ClearCompiledCache(
                    directory: $directory,
                    manifest : $manifest
                );

                $flow->clear(name: $name);
            }

            public function clearAll() : void
            {
                $directory = new CompiledCacheDirectory(path: $this->directory);
                $manifest  = CompiledCacheManifest::load(path: $directory->resolveManifestPath()->toString());

                $flow = new ClearCompiledCache(
                    directory: $directory,
                    manifest : $manifest
                );

                $flow->clearAll();
            }
        };
    }
}