<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Configuration;

use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\FileCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use InvalidArgumentException;

final readonly class CacheStoreConfiguration
{
    public function __construct(
        public string $type,
        public array $options = [],
    ) {}

    public static function inMemory() : self
    {
        return new self(type: 'memory');
    }

    public static function file(string $basePath) : self
    {
        return new self(type: 'file', options: ['base_path' => $basePath]);
    }

    public static function redis(string $host = '127.0.0.1', int $port = 6379) : self
    {
        return new self(type: 'redis', options: [
            'host' => $host,
            'port' => $port,
        ]);
    }

    public function build() : CacheStore
    {
        return match ($this->type) {
            'memory' => new InMemoryCacheStore(),
            'file'   => new FileCacheStore(
                basePath: $this->options['base_path'] ?? sys_get_temp_dir() . '/avax_cache',
            ),
            default => throw new InvalidArgumentException(message: 'Unknown store type: ' . $this->type),
        };
    }
}
