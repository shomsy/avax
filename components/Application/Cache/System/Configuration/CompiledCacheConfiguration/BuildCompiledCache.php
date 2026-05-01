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

final readonly class BuildCompiledCache
{
    public function __construct(private Clock $clock = new SystemClock())
    {
    }

    public function inDirectory(string $directory): CompiledCacheContract
    {
        return $this->fromConfiguration(CompiledCacheConfiguration::inDirectory($directory));
    }

    public function fromConfiguration(CompiledCacheConfiguration $compiledCacheConfiguration): CompiledCacheContract
    {
        return new readonly class ($compiledCacheConfiguration->directory, $this->clock) implements CompiledCacheContract {
            public function __construct(
                private string $directory,
                private Clock  $clock,
            ) {
            }

            public function read(string $name, callable $build, CompiledCacheSources $compiledCacheSources): mixed
            {
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString());

                $readCompiledCache = new ReadCompiledCache(
                    $compiledCacheDirectory,
                    $compiledCacheManifest,
                    $this->clock,
                );

                return $readCompiledCache->read($name, $build, $compiledCacheSources);
            }

            public function compile(string $name, callable $build, CompiledCacheSources $compiledCacheSources): CompiledCacheArtifact
            {
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString());

                $compileCache = new CompileCache(
                    $compiledCacheDirectory,
                    $compiledCacheManifest,
                    $this->clock,
                );

                return $compileCache->compile($name, $build, $compiledCacheSources);
            }

            public function clear(string $name): void
            {
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString());

                $clearCompiledCache = new ClearCompiledCache(
                    $compiledCacheDirectory,
                    $compiledCacheManifest,
                );

                $clearCompiledCache->clear($name);
            }

            public function clearAll(): void
            {
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString());

                $clearCompiledCache = new ClearCompiledCache(
                    $compiledCacheDirectory,
                    $compiledCacheManifest,
                );

                $clearCompiledCache->clearAll();
            }
        };
    }
}
