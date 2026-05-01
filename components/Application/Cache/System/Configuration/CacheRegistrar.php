<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Providers;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Configuration\BuildCache;
use Avax\Components\Application\Cache\System\Configuration\CacheConfiguration;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheFacade;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Components\Application\Container\System\Capabilities\Providers\BaseRegisterDependency;
use Override;

final class RegisterCacheDependencies extends BaseRegisterDependency
{
    private array $namedCaches = [];

    private string|null $compiledCacheDirectory = null;

    #[Override]
    public function register() : void
    {
        if ($this->namedCaches === []) {
            $this->namedCaches['default'] = ['store' => 'in_memory'];
        }

        $this->container->singleton(abstract: CacheRegistry::class, concrete: function () : CacheRegistry {
            $cacheRegistry = new CacheRegistry();

            foreach ($this->namedCaches as $name => $config) {
                $cacheRegistry->register($name, $this->buildNamedCache(name: $name, config: $config));
            }

            return $cacheRegistry;
        });

        $this->container->singleton(abstract: CacheFacade::class, concrete: function ($app) : CacheFacade {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new CacheFacade($app->get(id: CacheRegistry::class), $compiledCache);
        });

        $this->container->singleton(abstract: ReadFromCache::class, concrete: function ($app) : ReadFromCache {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new ReadFromCache($app->get(id: CacheRegistry::class), $compiledCache);
        });

        $this->container->singleton(abstract: CacheContract::class, concrete: static fn ($app) => $app->get(id: CacheRegistry::class)->default());

        if ($this->compiledCacheDirectory !== null) {
            $this->container->singleton(abstract: CompiledCacheContract::class, concrete: fn () : CompiledCacheContract => (new BuildCompiledCache())->inDirectory(directory: $this->compiledCacheDirectory));
        }

        Cache::use(cache: $this->container->get(id: CacheContract::class));
    }

    private function buildNamedCache(string $name, array $config) : CacheContract
    {
        $buildCache = new BuildCache();
        $cacheConfiguration = new CacheConfiguration(
            name      : $name,
            defaultTtl: is_int($config['ttl'] ?? null) ? $config['ttl'] : 3600,
        );

        return match ($config['store']) {
            'in_memory' => $buildCache->inMemory(
                config: $cacheConfiguration,
            ),
            'file'  => $buildCache->inDirectory(
                directory: is_string($config['directory'] ?? null) ? $config['directory'] : sys_get_temp_dir() . '/cache_' . $name,
                config   : $cacheConfiguration,
            ),
            'redis' => $buildCache->redis(
                host  : is_string($config['host'] ?? null) ? $config['host'] : '127.0.0.1',
                port  : is_int($config['port'] ?? null) ? $config['port'] : 6379,
                config: $cacheConfiguration,
            ),
            default => $buildCache->inMemory(config: $cacheConfiguration),
        };
    }

    public function defaultStore(string $store = 'in_memory', array $options = []) : self
    {
        $this->namedCaches['default'] = array_merge(['store' => $store], $options);

        return $this;
    }

    public function store(string $name, string $store = 'in_memory', array $options = []) : self
    {
        $this->namedCaches[$name] = array_merge(['store' => $store], $options);

        return $this;
    }

    public function compiledCacheDirectory(string $directory) : self
    {
        $this->compiledCacheDirectory = $directory;

        return $this;
    }
}
