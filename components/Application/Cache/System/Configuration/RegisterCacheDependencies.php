<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\PublicSurface\AvaxCache;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Cache\System\PublicSurface\CacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\CompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheFacade;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use LogicException;
use Override;

final class RegisterCacheDependencies extends BaseRegisterDependency
{
    /** @var array<string, array<string, mixed>> */
    private array $namedCaches = [];

    private string|null $compiledCacheDirectory = null;

    #[Override]
    public function register(): void
    {
        if ($this->namedCaches === []) {
            $this->namedCaches['default'] = ['store' => 'in_memory'];
        }

        $this->container->singleton(abstract: CacheRegistry::class, concrete: function (): CacheRegistry {
            $cacheRegistry = new CacheRegistry();

            foreach ($this->namedCaches as $name => $config) {
                $cacheRegistry->register($name, $this->buildNamedCache(name: $name, config: $config));
            }

            return $cacheRegistry;
        });

        $this->container->singleton(abstract: CacheFacade::class, concrete: function (ContainerInterface $app): CacheFacade {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(id: CacheRegistry::class);

            /** @var CompiledCacheContract|null $compiledCache */
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new CacheFacade($cacheRegistry, $compiledCache);
        });

        $this->container->singleton(abstract: ReadFromCache::class, concrete: function (ContainerInterface $app): ReadFromCache {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(id: CacheRegistry::class);

            /** @var CompiledCacheContract|null $compiledCache */
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new ReadFromCache($cacheRegistry, $compiledCache);
        });

        $this->container->singleton(abstract: CacheContract::class, concrete: static function (ContainerInterface $app): CacheContract {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(id: CacheRegistry::class);

            return $cacheRegistry->default();
        });

        if ($this->compiledCacheDirectory !== null) {
            $this->container->singleton(abstract: CompiledCacheContract::class, concrete: function (ContainerInterface $app) : CompiledCacheContract {
                if ($this->compiledCacheDirectory === null) {
                    throw new LogicException('Compiled cache directory was not configured.');
                }

                $clock      = $app->has(id: Clock::class) ? $app->get(id: Clock::class) : new SystemClock();
                $filesystem = $app->has(id: Filesystem::class) ? $app->get(id: Filesystem::class) : new Filesystem();

                return (new BuildCompiledCache(clock: $clock, filesystem: $filesystem))->inDirectory(directory: $this->compiledCacheDirectory);
            });
        }

        $cacheContract = $this->container->get(id: CacheContract::class);

        if (! $cacheContract instanceof CacheContract) {
            throw new LogicException('Cache contract registration did not resolve to a cache contract.');
        }

        Cache::use(cache: $cacheContract);

        if ($this->compiledCacheDirectory !== null) {
            $compiledCacheContract = $this->container->get(id: CompiledCacheContract::class);

            if (! $compiledCacheContract instanceof CompiledCacheContract) {
                throw new LogicException('Compiled cache registration did not resolve to a compiled cache contract.');
            }

            CompiledCache::use(compiledCacheContract: $compiledCacheContract);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function buildNamedCache(string $name, array $config): AvaxCache
    {
        $clock      = $this->container->has(id: Clock::class) ? $this->container->get(id: Clock::class) : new SystemClock();
        $filesystem = $this->container->has(id: Filesystem::class) ? $this->container->get(id: Filesystem::class) : new Filesystem();

        $buildCache = new BuildCache(clock: $clock, filesystem: $filesystem);
        $cacheConfiguration = new CacheConfiguration(
            name: $name,
            defaultTtl: is_int($config['ttl'] ?? null) ? $config['ttl'] : 3600,
        );

        return match ($config['store']) {
            'in_memory' => $buildCache->inMemory(
                config: $cacheConfiguration,
            ),
            'file' => $buildCache->inDirectory(
                directory: is_string($config['directory'] ?? null) ? $config['directory'] : sys_get_temp_dir().'/cache_'.$name,
                config: $cacheConfiguration,
            ),
            'redis' => $buildCache->redis(
                host: is_string($config['host'] ?? null) ? $config['host'] : '127.0.0.1',
                port: is_int($config['port'] ?? null) ? $config['port'] : 6379,
                config: $cacheConfiguration,
            ),
            default => $buildCache->inMemory(config: $cacheConfiguration),
        };
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function defaultStore(string $store = 'in_memory', array $options = []): self
    {
        $this->namedCaches['default'] = array_merge(['store' => $store], $options);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function store(string $name, string $store = 'in_memory', array $options = []): self
    {
        $this->namedCaches[$name] = array_merge(['store' => $store], $options);

        return $this;
    }

    public function compiledCacheDirectory(string $directory): self
    {
        $this->compiledCacheDirectory = $directory;

        return $this;
    }
}
