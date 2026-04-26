<?php

declare(strict_types=1);

namespace components\Cache\Tests\Unit\Cache\Capabilities\Observability\IdentifyCachedValues;

use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheVersion;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\InvalidCacheKey;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase
{
    public function test_creates_valid_key() : void
    {
        $key = CacheKey::create(key: 'valid_key-123');

        $this->assertSame(expected: 'valid_key-123', actual: $key->toString());
    }

    public function test_normalizes_key() : void
    {
        $key = CacheKey::create(key: 'UPPER_CASE');

        $this->assertSame(expected: 'upper_case', actual: $key->toString());
    }

    public function test_rejects_empty_key() : void
    {
        $this->expectException(exception: InvalidCacheKey::class);
        $this->expectExceptionMessage(message: 'System key must be at least 1 character(s)');

        CacheKey::create(key: '');
    }

    public function test_rejects_key_with_invalid_characters() : void
    {
        $this->expectException(exception: InvalidCacheKey::class);
        $this->expectExceptionMessage(message: 'System key contains invalid characters');

        CacheKey::create(key: 'invalid key with spaces');
    }

    public function test_rejects_key_exceeding_max_length() : void
    {
        $longKey = str_repeat('a', 257);

        $this->expectException(exception: InvalidCacheKey::class);
        $this->expectExceptionMessage(message: 'System key must not exceed 256 characters');

        CacheKey::create(key: $longKey);
    }

    public function test_creates_key_from_parts() : void
    {
        $key = CacheKey::fromParts('user', 'profile', '123');

        $this->assertSame(expected: 'user:profile:123', actual: $key->toString());
    }

    public function test_adds_namespace_to_key() : void
    {
        $key        = CacheKey::create(key: 'profile');
        $namespaced = $key->withNamespace(namespace: 'users');

        $this->assertSame(expected: 'users:profile', actual: $namespaced->fullKey());
    }

    public function test_full_key_includes_namespace_and_version() : void
    {
        $key = CacheKey::create(
            key      : 'data',
            namespace: 'app',
            version  : new CacheVersion(major: 1, minor: 2, patch: 3)
        );

        $this->assertSame(expected: 'app:data:v1.2.3', actual: $key->fullKey());
    }
}