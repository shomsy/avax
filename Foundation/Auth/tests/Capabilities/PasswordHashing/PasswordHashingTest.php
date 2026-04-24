<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\PasswordHashing;

use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for PasswordHashing capability.
 */
class PasswordHashingTest extends TestCase
{
    public function testDefaultHasherPrefersArgon2idWhenAvailable() : void
    {
        $hasher = new PasswordHasher();
        $hash   = $hasher->hash(password: 'secret');
        $info   = password_get_info(hash: $hash);

        if (defined(constant_name: 'PASSWORD_ARGON2ID')) {
            $this->assertSame(expected: 'argon2id', actual: $info['algoName']);

            return;
        }

        $this->assertNotSame(expected: 'unknown', actual: $info['algoName']);
    }

    public function testHashAndVerify() : void
    {
        $hasher   = new PasswordHasher();
        $password = 'secret';
        $hash     = $hasher->hash(password: $password);

        $this->assertNotSame(expected: $password, actual: $hash);
        $this->assertTrue(condition: $hasher->verify(password: $password, hash: $hash));
        $this->assertFalse(condition: $hasher->verify(password: 'wrong', hash: $hash));
    }

    public function testNeedsRehash() : void
    {
        $hasher = new PasswordHasher(options: ['cost' => 10]);
        $hash   = $hasher->hash(password: 'secret');

        $this->assertFalse(condition: $hasher->needsRehash(hash: $hash));

        $newHasher = new PasswordHasher(options: ['cost' => 12]);
        $this->assertTrue(condition: $newHasher->needsRehash(hash: $hash));
    }
}
