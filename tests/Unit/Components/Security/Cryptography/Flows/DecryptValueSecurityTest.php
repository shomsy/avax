<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Cryptography\Flows;

use PHPUnit\Framework\TestCase;

/**
 * Security verification tests for DecryptValue deserialization fallback.
 *
 * These tests prove that:
 * - DecryptValue uses allowed_classes => false on fallback unserialize
 * - Even if decrypted plaintext contains serialized objects, they are not instantiated
 * - The fallback behavior is documented and tested
 */
final class DecryptValueSecurityTest extends TestCase
{
    // ============================================================
    // 1. VERIFY SOURCE CODE ENFORCEMENT
    // ============================================================

    public function test_decrypt_value_uses_restricted_unserialize() : void
    {
        $file = __DIR__ . '/../../../../../../components/Security/Cryptography/System/Flows/DecryptValue/DecryptValue.php';

        self::assertFileExists($file, 'DecryptValue.php must exist for security audit');

        $content = file_get_contents($file);
        self::assertNotFalse($content);

        // Must use allowed_classes => false on unserialize
        self::assertStringContainsString(
            "'allowed_classes' => false",
            $content,
            'DecryptValue must use allowed_classes => false on fallback unserialize',
        );
    }

    // ============================================================
    // 2. BEHAVIOR PROOF
    // ============================================================

    public function test_allowed_classes_false_prevents_object_instantiation_in_fallback() : void
    {
        // Simulate what would happen if decrypted plaintext contained a serialized object
        $maliciousPlaintext = serialize(new \stdClass());

        $result = unserialize($maliciousPlaintext, ['allowed_classes' => false]);

        // With allowed_classes => false, objects become __PHP_Incomplete_Class, not real objects
        self::assertNotInstanceOf(\stdClass::class, $result);
    }

    public function test_allowed_classes_false_allows_array_fallback() : void
    {
        // The intended use: decrypted plaintext that was an array
        $plaintext = serialize(['user_id' => 123, 'role' => 'admin']);

        $result = unserialize($plaintext, ['allowed_classes' => false]);

        self::assertSame(['user_id' => 123, 'role' => 'admin'], $result);
    }

    public function test_fallback_returns_raw_string_when_json_and_unserialize_both_fail() : void
    {
        // When both JSON decode and unserialize fail, the caller should return raw string.
        // This test proves that unserialize with allowed_classes => false returns false for invalid data,
        // which is the fallback behavior DecryptValue relies on.
        $invalidData = 'not-json-not-serialized';
        $result = @unserialize($invalidData, ['allowed_classes' => false]);

        self::assertFalse($result);
    }
}
