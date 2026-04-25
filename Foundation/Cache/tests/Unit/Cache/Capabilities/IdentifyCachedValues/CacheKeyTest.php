<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Capabilities\IdentifyCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheVersion;
use Avax\Cache\System\Capabilities\IdentifyCachedValues\InvalidCacheKey;
use PHPUnit\Framework\TestCase;

final class CacheKeyTest extends TestCase
{
    public function test_creates_valid_key() : void
    {
        $key = CacheKey::create('valid_key-123');

        $this->assertSame('valid_key-123', $key->toString());
    }

    public function test_normalizes_key() : void
    {
        $key = CacheKey::create('UPPER_CASE');

        $this->assertSame('upper_case', $key->toString());
    }

    public function test_rejects_empty_key() : void
    {
        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key must be at least 1 character(s)');

        CacheKey::create('');
    }

    public function test_rejects_key_with_invalid_characters() : void
    {
        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key contains invalid characters');

        CacheKey::create('invalid key with spaces');
    }

    public function test_rejects_key_exceeding_max_length() : void
    {
        $longKey = str_repeat('a', 257);

        $this->expectException(InvalidCacheKey::class);
        $this->expectExceptionMessage('System key must not exceed 256 characters');

        CacheKey::create($longKey);
    }

    public function test_creates_key_from_parts() : void
    {
        $key = CacheKey::fromParts('user', 'profile', '123');

        $this->assertSame('user:profile:123', $key->toString());
    }

    public function test_adds_namespace_to_key() : void
    {
        $key        = CacheKey::create('profile');
        $namespaced = $key->withNamespace('users');

        $this->assertSame('users:profile', $namespaced->fullKey());
    }

    public function test_full_key_includes_namespace_and_version() : void
    {
        $key = CacheKey::create(
                       'data',
            namespace: 'app',
            version  : new CacheVersion(1, 2, 3)
        );

        $this->assertSame('app:data:v1.2.3', $key->fullKey());
    }
}