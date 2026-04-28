<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

final readonly class CacheKeyPrefix
{
    private string $prefix;

    public function __construct(
        string        $prefix,
        public string $separator = ':'
    )
    {
        $this->prefix = trim($prefix);

        if ($this->prefix !== '' && ! str_ends_with($this->prefix, $this->separator)) {
            $this->prefix .= $this->separator;
        }
    }

    public static function fromNamespace(CacheNamespace $namespace, string $separator = ':') : self
    {
        return new self(prefix: $namespace->toString(), separator: $separator);
    }

    public function toString() : string
    {
        return $this->prefix;
    }

    public function prepend(string $key) : CacheKey
    {
        return CacheKey::create(
            key      : $this->prefix . $key,
            namespace: null,
            version  : null
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

    public function __toString() : string
    {
        return $this->toString();
    }
}