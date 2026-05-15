<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Foundation\Serialization\JsonCacheSerializer;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use InvalidArgumentException;

final readonly class CacheStoreConfiguration
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $type,
        public array $options = [],
    ) {
    }

    public static function inMemory(): self
    {
        return new self(type: 'memory');
    }

    public static function file(string $basePath): self
    {
        return new self(type: 'file', options: ['base_path' => $basePath]);
    }

    public static function redis(string $host = '127.0.0.1', int $port = 6379): self
    {
        return new self(type: 'redis', options: [
            'host' => $host,
            'port' => $port,
        ]);
    }

    /**
 * @throws InvalidArgumentException
 */
public function build(): CacheStore
    {
        $basePath = is_string($this->options['base_path'] ?? null)
            ? $this->options['base_path']
            : sys_get_temp_dir().'/avax_cache';

        return match ($this->type) {
            'memory' => new InMemoryCacheStore(
                clock                          : new SystemClock(),
                chooseCachedValueForReplacement: new LeastRecentlyUsedReplacement(),
            ),
            'file' => new FileCacheStore(
                basePath  : $basePath,
                filesystem: new Filesystem(),
                clock     : new SystemClock(),
                jsonCacheSerializer: new JsonCacheSerializer(
                                clock: new SystemClock(),
                            ),
            ),
            default => throw new InvalidArgumentException(message: 'Unknown store type: '.$this->type),
        };
    }
}
