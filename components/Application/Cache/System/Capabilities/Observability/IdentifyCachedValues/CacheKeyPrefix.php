<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use Override;
use Stringable;

final readonly class CacheKeyPrefix implements Stringable
{
    private string $prefix;

    public function __construct(
        string $prefix,
        public string $separator = ':',
    )
    {
        $normalizedPrefix = trim($prefix);

        if ($normalizedPrefix !== '' && ! str_ends_with($normalizedPrefix, $this->separator)) {
            $normalizedPrefix .= $this->separator;
        }

        $this->prefix = $normalizedPrefix;
    }

    public static function fromNamespace(CacheNamespace $cacheNamespace, string $separator = ':') : self
    {
        return new self(prefix: $cacheNamespace->toString(), separator: $separator);
    }

    public function toString() : string
    {
        return $this->prefix;
    }

    public function prepend(string $key) : CacheKey
    {
        return CacheKey::create(
            key         : $this->prefix . $key,
            namespace   : null,
            cacheVersion: null,
        );
    }

    public static function create(string $prefix, string $separator = ':') : self
    {
        return new self(prefix: $prefix, separator: $separator);
    }

    public function strip(string $fullKey) : string
    {
        if (! $this->matches(fullKey: $fullKey)) {
            return $fullKey;
        }

        return substr($fullKey, strlen($this->prefix));
    }

    public function matches(string $fullKey) : bool
    {
        return str_starts_with($fullKey, $this->prefix);
    }

    #[Override]
    public function __toString() : string
    {
        return $this->prefix;
    }
}
