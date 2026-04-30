<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Override;
use Stringable;

final readonly class CacheSourceKey implements Stringable
{
    public function __construct(
        public string      $key,
        public string|null $namespace = null,
    ) {}

    public static function create(string $key, string|null $namespace = null) : self
    {
        return new self(key: $key, namespace: $namespace);
    }

    #[Override]
    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return $this->fullKey();
    }

    public function fullKey() : string
    {
        if ($this->namespace === null) {
            return $this->key;
        }

        return sprintf('%s:%s', $this->namespace, $this->key);
    }
}
