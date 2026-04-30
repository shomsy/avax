<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Providers;

use Avax\Components\Application\Cache\Cache;
use Avax\Components\Application\Cache\System\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Configuration\BuildCache;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheFacade;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Components\Application\Container\Providers\ServiceProvider;

final class CacheServiceProvider extends ServiceProvider
{
    private array $namedCaches = [];

    private string|null $compiledCacheDirectory = null;

    public function register() : void
    {
        if ($this->namedCaches === []) {
            $this->namedCaches['default'] = ['store' => 'in_memory'];
        }

        $this->app->singleton(id: CacheRegistry::class, implementation: function () : CacheRegistry {
            $cacheRegistry = new CacheRegistry();

            foreach ($this->namedCaches as $name => $config) {
                $cacheRegistry->register($name, $this->buildNamedCache(name: $name, config: $config));
            }

            return $cacheRegistry;
        });

        $this->app->singleton(id: CacheFacade::class, implementation: function ($app) : CacheFacade {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new CacheFacade($app->get(id: CacheRegistry::class), $compiledCache);
        });

        $this->app->singleton(id: ReadFromCache::class, implementation: function ($app) : ReadFromCache {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(id: CompiledCacheContract::class)
                : null;

            return new ReadFromCache($app->get(id: CacheRegistry::class), $compiledCache);
        });

        $this->app->singleton(id: CacheContract::class, implementation: static fn ($app) => $app->get(id: CacheRegistry::class)->default());

        if ($this->compiledCacheDirectory !== null) {
            $this->app->singleton(id: CompiledCacheContract::class, implementation: fn () : CompiledCacheContract => (new BuildCompiledCache())->inDirectory(directory: $this->compiledCacheDirectory));
        }

        Cache::use(cache: $this->app->get(id: CacheContract::class));
    }

    private function buildNamedCache(string $name, array $config) : CacheContract
    {
        $buildCache = new BuildCache();

        return match ($config['store']) {
            'in_memory' => $buildCache->inMemory(
                $config['ttl'] ?? 3600,
            ),
            'file'      => $buildCache->inDirectory(
                $config['directory'] ?? sys_get_temp_dir() . '/cache_' . $name,
                $config['ttl'] ?? 3600,
            ),
            'redis'     => $buildCache->redis(
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 6379,
                $config['ttl'] ?? 3600,
            ),
            default     => $buildCache->inMemory(config: $config['ttl'] ?? 3600),
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
