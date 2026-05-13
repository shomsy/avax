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
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

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
                private Clock $clock,
            ) {
            }

            public function read(string $name, callable $build, CompiledCacheSources $sources): mixed
            {
                $filesystem             = new Filesystem();
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory, filesystem: $filesystem);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString(), filesystem: $filesystem);

                $readCompiledCache = new ReadCompiledCache(
                    compiledCacheDirectory: $compiledCacheDirectory,
                    compiledCacheManifest : $compiledCacheManifest,
                    filesystem            : $filesystem,
                    clock                 : $this->clock,
                );

                return $readCompiledCache->read(name: $name, build: $build, compiledCacheSources: $sources);
            }

            public function compile(string $name, callable $build, CompiledCacheSources $sources): CompiledCacheArtifact
            {
                $filesystem             = new Filesystem();
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory, filesystem: $filesystem);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString(), filesystem: $filesystem);

                $compileCache = new CompileCache(
                    compiledCacheDirectory: $compiledCacheDirectory,
                    compiledCacheManifest : $compiledCacheManifest,
                    filesystem            : $filesystem,
                    clock                 : $this->clock,
                );

                return $compileCache->compile(name: $name, build: $build, compiledCacheSources: $sources);
            }

            public function clear(string $name): void
            {
                $filesystem             = new Filesystem();
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory, filesystem: $filesystem);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString(), filesystem: $filesystem);

                $clearCompiledCache = new ClearCompiledCache(
                    compiledCacheDirectory: $compiledCacheDirectory,
                    compiledCacheManifest : $compiledCacheManifest,
                    filesystem            : $filesystem,
                );

                $clearCompiledCache->clear($name);
            }

            public function clearAll(): void
            {
                $filesystem             = new Filesystem();
                $compiledCacheDirectory = new CompiledCacheDirectory(path: $this->directory, filesystem: $filesystem);
                $compiledCacheManifest  = CompiledCacheManifest::load(path: $compiledCacheDirectory->resolveManifestPath()->toString(), filesystem: $filesystem);

                $clearCompiledCache = new ClearCompiledCache(
                    compiledCacheDirectory: $compiledCacheDirectory,
                    compiledCacheManifest : $compiledCacheManifest,
                    filesystem            : $filesystem,
                );

                $clearCompiledCache->clearAll();
            }
        };
    }
}
