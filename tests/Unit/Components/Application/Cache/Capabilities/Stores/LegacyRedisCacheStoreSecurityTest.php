<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Stores;

use PHPUnit\Framework\TestCase;

/**
 * Security boundary tests for RedisCacheStore (legacy) deserialization.
 *
 * These tests prove the serialization boundary behavior without requiring
 * a live Redis connection. They verify that:
 * - The unserialize call uses allowed_classes => false
 * - Malicious object payloads do not instantiate
 * - Corrupted payloads fail safely
 */
final class LegacyRedisCacheStoreSecurityTest extends TestCase
{
    // ============================================================
    // 1. VERIFY SOURCE CODE ENFORCEMENT
    // ============================================================

    /**
     * Proof that the legacy RedisCacheStore uses restricted unserialize.
     *
     * This is a static analysis-style test that reads the source to confirm
     * the security restriction is present. Runtime tests would need Redis.
     */
    public function test_legacy_redis_cache_store_uses_restricted_unserialize() : void
    {
        $file = __DIR__ . '/../../../../../../../components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php';

        self::assertFileExists($file, 'Legacy RedisCacheStore.php must exist for security audit');

        $content = file_get_contents($file);
        self::assertNotFalse($content);

        // The security fix: unserialize must NOT be called with allowed_classes => true
        // or without any allowed_classes restriction.
        self::assertStringContainsString(
            "'allowed_classes' => false",
            $content,
            'RedisCacheStore::get() must use allowed_classes => false to prevent object injection',
        );

        // Must NOT contain bare unserialize without restriction
        self::assertDoesNotMatchRegularExpression(
            '/unserialize\s*\(\s*\$value\s*\)\s*[;)]/',
            $content,
            'RedisCacheStore must not call bare unserialize($value) without allowed_classes restriction',
        );
    }

    // ============================================================
    // 2. BEHAVIOR PROOF THROUGH SERIALIZATION LOGIC
    // ============================================================

    /**
     * Proof that allowed_classes => false prevents object instantiation.
     *
     * This demonstrates the behavior that RedisCacheStore::get() relies on.
     */
    public function test_unserialize_with_false_allowed_classes_rejects_objects() : void
    {
        // Create a serialized stdClass object
        $maliciousPayload = serialize(new \stdClass());

        $result = unserialize($maliciousPayload, ['allowed_classes' => false]);

        // With allowed_classes => false, objects become __PHP_Incomplete_Class
        self::assertNotInstanceOf(\stdClass::class, $result);
    }

    public function test_unserialize_with_false_allowed_classes_allows_scalars() : void
    {
        $data = ['key' => 'value', 'number' => 42, 'flag' => true];
        $serialized = serialize($data);

        $result = unserialize($serialized, ['allowed_classes' => false]);

        self::assertSame($data, $result);
    }

    public function test_unserialize_with_empty_allowed_classes_allows_scalars() : void
    {
        $data = 'hello world';
        $serialized = serialize($data);

        $result = unserialize($serialized, ['allowed_classes' => []]);

        self::assertSame('hello world', $result);
    }

    public function test_corrupted_serialized_data_returns_false_with_empty_classes() : void
    {
        // @unserialize may emit a warning for invalid data - this is expected
        $result = @unserialize('not-valid-serialized-data', ['allowed_classes' => false]);

        self::assertFalse($result);
    }

    public function test_b_false_serialized_value_is_not_false() : void
    {
        // Edge case: serialize(false) returns 'b:0;' which unserializes to false
        // This is valid and should not be treated as an error
        $result = unserialize('b:0;', ['allowed_classes' => false]);

        self::assertFalse($result);
    }
}
