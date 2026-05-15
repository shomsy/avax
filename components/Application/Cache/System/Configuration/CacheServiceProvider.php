<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\PublicSurface\AvaxCache;
use Avax\Components\Application\Cache\System\PublicSurface\CacheContract;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\Configuration\Builders\BuildCache;
use Avax\Components\Application\Cache\System\Configuration\CacheConfiguration;
use Avax\Components\Application\Cache\System\Configuration\CompiledCacheConfiguration\Builders\BuildCompiledCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\PublicSurface\Cache;
use Avax\Components\Application\Cache\System\PublicSurface\CompiledCache;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheFacade;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;
use Avax\Components\Application\Cache\System\PublicSurface\Read\ReadFromCache;
use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use LogicException;

/**
 * CacheServiceProvider — registers cache component dependencies.
 *
 * Supports fluent configuration before registration:
 *   $provider = new CacheServiceProvider();
 *   $provider->defaultStore('redis', ['host' => '127.0.0.1']);
 *   $provider->compiledCacheDirectory('/tmp/cache');
 */
final class CacheServiceProvider implements ServiceProvider
{
    /** @var array<string, array<string, mixed>> */
    private array $namedCaches = [];

    private string|null $compiledCacheDirectory = null;

    public function register(ContainerInterface $container) : void
    {
        if ($this->namedCaches === []) {
            $this->namedCaches['default'] = ['store' => 'in_memory'];
        }

        // Register default Clock if not already bound — composition root exception
        if (! $container->has(Clock::class)) {
            $container->singleton(Clock::class, static fn () : SystemClock => new SystemClock());
        }

        // Register default Filesystem if not already bound — composition root exception
        if (! $container->has(Filesystem::class)) {
            $container->singleton(Filesystem::class, static fn () : Filesystem => new Filesystem());
        }

        $container->singleton(CacheRegistry::class, function () use ($container) : CacheRegistry {
            $registry = new CacheRegistry();

            foreach ($this->namedCaches as $name => $config) {
                $registry->register($name, $this->buildNamedCache(name: $name, config: $config, container: $container));
            }

            return $registry;
        });

        $container->singleton(CacheFacade::class, function (ContainerInterface $app) : CacheFacade {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(CacheRegistry::class);

            /** @var CompiledCacheContract|null $compiledCache */
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(CompiledCacheContract::class)
                : null;

            return new CacheFacade($cacheRegistry, $compiledCache);
        });

        $container->singleton(ReadFromCache::class, function (ContainerInterface $app) : ReadFromCache {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(CacheRegistry::class);

            /** @var CompiledCacheContract|null $compiledCache */
            $compiledCache = $this->compiledCacheDirectory !== null
                ? $app->get(CompiledCacheContract::class)
                : null;

            return new ReadFromCache($cacheRegistry, $compiledCache);
        });

        $container->singleton(CacheContract::class, static function (ContainerInterface $app) : CacheContract {
            /** @var CacheRegistry $cacheRegistry */
            $cacheRegistry = $app->get(CacheRegistry::class);

            return $cacheRegistry->default();
        });

        if ($this->compiledCacheDirectory !== null) {
            $directory = $this->compiledCacheDirectory;
            $container->singleton(CompiledCacheContract::class, function (ContainerInterface $app) use ($directory) : CompiledCacheContract {
                $clock      = $app->get(Clock::class);
                $filesystem = $app->get(Filesystem::class);

                return (new BuildCompiledCache(clock: $clock, filesystem: $filesystem))->inDirectory(directory: $directory);
            });
        }

        // Wire static facades for backward compatibility — boot-time behavior, deferred to boot()
    }

    public function boot(ContainerInterface $container) : void
    {
        // Wire static facades after all registrations are complete
        $cacheContract = $container->get(CacheContract::class);

        if (! $cacheContract instanceof CacheContract) {
            throw new LogicException('Cache contract registration did not resolve to a cache contract.');
        }

        Cache::use(cache: $cacheContract);

        if ($this->compiledCacheDirectory !== null) {
            $compiledCacheContract = $container->get(CompiledCacheContract::class);

            if (! $compiledCacheContract instanceof CompiledCacheContract) {
                throw new LogicException('Compiled cache registration did not resolve to a compiled cache contract.');
            }

            CompiledCache::use(compiledCacheContract: $compiledCacheContract);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function buildNamedCache(string $name, array $config, ContainerInterface $container) : AvaxCache
    {
        $builder = new BuildCache(
            clock     : $container->get(Clock::class),
            filesystem: $container->get(Filesystem::class),
        );
        $cacheConfiguration = new CacheConfiguration(
            name      : $name,
            defaultTtl: is_int($config['ttl'] ?? null) ? $config['ttl'] : 3600,
        );

        return match ($config['store']) {
            'in_memory' => $builder->inMemory(config: $cacheConfiguration),
            'file' => $builder->inDirectory(
                directory: is_string($config['directory'] ?? null) ? $config['directory'] : sys_get_temp_dir() . '/cache_' . $name,
                config   : $cacheConfiguration,
            ),
            'redis' => $builder->redis(
                host  : is_string($config['host'] ?? null) ? $config['host'] : '127.0.0.1',
                port  : is_int($config['port'] ?? null) ? $config['port'] : 6379,
                config: $cacheConfiguration,
            ),
            default => $builder->inMemory(config: $cacheConfiguration),
        };
    }

    /**
     * Configure the default cache store.
     *
     * @param  array<string, mixed>  $options
     */
    public function defaultStore(string $store = 'in_memory', array $options = []) : self
    {
        $this->namedCaches['default'] = array_merge(['store' => $store], $options);

        return $this;
    }

    /**
     * Configure a named cache store.
     *
     * @param  array<string, mixed>  $options
     */
    public function store(string $name, string $store = 'in_memory', array $options = []) : self
    {
        $this->namedCaches[$name] = array_merge(['store' => $store], $options);

        return $this;
    }

    /**
     * Configure the compiled cache directory.
     */
    public function compiledCacheDirectory(string $directory) : self
    {
        $this->compiledCacheDirectory = $directory;

        return $this;
    }
}
