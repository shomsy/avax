<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Observability\IdentifyCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheVersion;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\InvalidCacheKey;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase
{
    public function test_creates_valid_key() : void
    {
        $cacheKey = CacheKey::create(key: 'valid_key-123');

        $this->assertSame('valid_key-123', $cacheKey->toString());
    }

    public function test_normalizes_key() : void
    {
        $cacheKey = CacheKey::create(key: 'UPPER_CASE');

        $this->assertSame('upper_case', $cacheKey->toString());
    }

    public function test_rejects_empty_key() : void
    {
        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key must be at least 1 character(s)');

        CacheKey::create(key: '');
    }

    public function test_rejects_key_with_invalid_characters() : void
    {
        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key contains invalid characters');

        CacheKey::create(key: 'invalid key with spaces');
    }

    public function test_rejects_key_exceeding_max_length() : void
    {
        $longKey = str_repeat('a', 257);

        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key must not exceed 256 characters');

        CacheKey::create(key: $longKey);
    }

    public function test_creates_key_from_parts() : void
    {
        $cacheKey = CacheKey::fromParts('user', 'profile', '123');

        $this->assertSame('user:profile:123', $cacheKey->toString());
    }

    public function test_adds_namespace_to_key() : void
    {
        $cacheKey   = CacheKey::create(key: 'profile');
        $namespaced = $cacheKey->withNamespace(namespace: 'users');

        $this->assertSame('users:profile', $namespaced->fullKey());
    }

    public function test_full_key_includes_namespace_and_version() : void
    {
        $cacheKey = CacheKey::create(
            key      : 'data',
            namespace: 'app',
            version  : new CacheVersion(major: 1, minor: 2, patch: 3),
        );

        $this->assertSame('app:data:v1.2.3', $cacheKey->fullKey());
    }
}
