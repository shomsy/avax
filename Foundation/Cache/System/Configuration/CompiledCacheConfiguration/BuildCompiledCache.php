<?php

declare(strict_types=1);

namespace Avax\Cache\System\Configuration\CompiledCacheConfiguration;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Flows\ClearCompiledCache\ClearCompiledCache;
use Avax\Cache\System\Flows\CompileCache\CompileCache;
use Avax\Cache\System\Flows\ReadCompiledCache\ReadCompiledCache;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

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
            CompiledCacheConfiguration::inDirectory($directory)
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
                $directory = new CompiledCacheDirectory($this->directory);
                $manifest  = CompiledCacheManifest::load($directory->resolveManifestPath()->toString());

                $flow = new ReadCompiledCache(
                    $directory,
                    $manifest,
                    $this->clock
                );

                return $flow->read($name, $build, $sources);
            }

            public function compile(string $name, callable $build, CompiledCacheSources $sources) : CompiledCacheArtifact
            {
                $directory = new CompiledCacheDirectory($this->directory);
                $manifest  = CompiledCacheManifest::load($directory->resolveManifestPath()->toString());

                $flow = new CompileCache(
                    $directory,
                    $manifest,
                    $this->clock
                );

                return $flow->compile($name, $build, $sources);
            }

            public function clear(string $name) : void
            {
                $directory = new CompiledCacheDirectory($this->directory);
                $manifest  = CompiledCacheManifest::load($directory->resolveManifestPath()->toString());

                $flow = new ClearCompiledCache(
                    $directory,
                    $manifest
                );

                $flow->clear($name);
            }

            public function clearAll() : void
            {
                $directory = new CompiledCacheDirectory($this->directory);
                $manifest  = CompiledCacheManifest::load($directory->resolveManifestPath()->toString());

                $flow = new ClearCompiledCache(
                    $directory,
                    $manifest
                );

                $flow->clearAll();
            }
        };
    }
}