<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use Stringable;

final readonly class CacheKey implements Stringable
{
    private const MIN_LENGTH    = 1;
    private const MAX_LENGTH    = 256;
    private const VALID_PATTERN = '/^[a-zA-Z0-9_\-\.:]+$/';

    private string $normalized;

    public function __construct(
        public string            $original,
        public string|null       $namespace = null,
        public CacheVersion|null $version = null
    )
    {
        $this->normalized = $this->normalize(key: $original);
        $this->validate(key: $this->normalized);
    }

    private function normalize(string $key) : string
    {
        $normalized = trim($key);

        if ($normalized !== $key) {
            $normalized = preg_replace('/\s+/', '_', $normalized) ?? $normalized;
        }

        return strtolower($normalized);
    }

    private function validate(string $key) : void
    {
        $length = strlen($key);

        if ($length < self::MIN_LENGTH) {
            throw new InvalidCacheKey(
                message: sprintf('System key must be at least %d character(s)', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidCacheKey(
                message: sprintf('System key must not exceed %d characters', self::MAX_LENGTH)
            );
        }

        if (! preg_match(self::VALID_PATTERN, $key)) {
            throw new InvalidCacheKey(
                message: 'System key contains invalid characters. Only alphanumeric, underscore, dash, dot, and colon are allowed'
            );
        }
    }

    public static function create(string $key, string|null $namespace = null, CacheVersion|null $version = null) : self
    {
        return new self(original: $key, namespace: $namespace, version: $version);
    }

    public static function fromParts(string ...$parts) : self
    {
        $key = implode(':', $parts);

        return new self(original: $key);
    }

    public function withNamespace(string $namespace) : self
    {
        return new self(
            original : $this->original,
            namespace: $namespace,
            version  : $this->version
        );
    }

    public function withVersion(CacheVersion $version) : self
    {
        return new self(
            original : $this->original,
            namespace: $this->namespace,
            version  : $version
        );
    }

    public function matchesPattern(string $pattern) : bool
    {
        $regex = $this->patternToRegex(pattern: $pattern);

        return preg_match($regex, $this->fullKey()) === 1;
    }

    private function patternToRegex(string $pattern) : string
    {
        $escaped = preg_quote($pattern, delimiter: '/');

        $escaped = str_replace(
            ['\\*', '\\?'],
            ['.*', '.'],
            $escaped
        );

        return '/^' . $escaped . '$/';
    }

    public function fullKey() : string
    {
        $parts = [];

        if ($this->namespace !== null) {
            $parts[] = $this->namespace;
        }

        $parts[] = $this->normalized;

        if ($this->version !== null) {
            $parts[] = 'v' . $this->version->toString();
        }

        return implode(separator: ':', array: $parts);
    }

    public function toString() : string
    {
        return $this->normalized;
    }

    public function __toString() : string
    {
        return $this->fullKey();
    }
}