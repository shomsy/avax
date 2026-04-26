<?php

declare(strict_types=1);

namespace Avax\Cache\Providers;

use Avax\Cache\Cache;
use Avax\Cache\System\CacheContract;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheContract;
use Avax\Cache\System\Configuration\BuildCache;
use Avax\Cache\System\Configuration\CompiledCacheConfiguration\BuildCompiledCache;
use Avax\Cache\System\PublicSurface\CacheFacade;
use Avax\Cache\System\PublicSurface\CacheRegistry;
use Avax\Cache\System\PublicSurface\ReadFromCache;
use Avax\Container\Providers\ServiceProvider;

final class CacheServiceProvider extends ServiceProvider
{
    private array $namedCaches = [];
    private ?string $compiledCacheDirectory = null;

    public function register() : void
    {
        if ($this->namedCaches === []) {
            $this->namedCaches['default'] = ['store' => 'in_memory'];
        }

        $this->app->singleton(CacheRegistry::class, function () {
            $registry = new CacheRegistry();

            foreach ($this->namedCaches as $name => $config) {
                $registry->register($name, $this->buildNamedCache($name, $config));
            }

            return $registry;
        });

        $this->app->singleton(CacheFacade::class, function ($app) {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->make(CompiledCacheContract::class)
                : null;

            return new CacheFacade($app->make(CacheRegistry::class), $compiledCache);
        });

        $this->app->singleton(ReadFromCache::class, function ($app) {
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->make(CompiledCacheContract::class)
                : null;

            return new ReadFromCache($app->make(CacheRegistry::class), $compiledCache);
        });

        $this->app->singleton(CacheContract::class, function ($app) {
            return $app->make(CacheRegistry::class)->default();
        });

        if ($this->compiledCacheDirectory !== null) {
            $this->app->singleton(CompiledCacheContract::class, function () {
                return (new BuildCompiledCache())->inDirectory($this->compiledCacheDirectory);
            });
        }

        Cache::use($this->app->make(CacheContract::class));
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

    private function buildNamedCache(string $name, array $config) : CacheContract
    {
        $builder = new BuildCache();

        return match ($config['store']) {
            'in_memory' => $builder->inMemory(
                $config['ttl'] ?? 3600,
                $config['max_entries'] ?? 1000
            ),
            'file'  => $builder->inDirectory(
                $config['directory'] ?? sys_get_temp_dir() . '/cache_' . $name,
                $config['ttl'] ?? 3600
            ),
            'redis' => $builder->redis(
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? 6379,
                $config['ttl'] ?? 3600
            ),
            default => $builder->inMemory($config['ttl'] ?? 3600),
        };
    }
}