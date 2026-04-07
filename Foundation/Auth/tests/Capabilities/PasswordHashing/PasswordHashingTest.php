<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\PasswordHashing;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;

/**
 * Unit test for PasswordHashing capability.
 */
class PasswordHashingTest extends TestCase
{
    public function testHashAndVerify() : void
    {
        $hasher = new PasswordHasher();
        $password = 'secret';
        $hash = $hasher->hash(password: $password);

        $this->assertNotSame(expected: $password, actual: $hash);
        $this->assertTrue(condition: $hasher->verify(password: $password, hash: $hash));
        $this->assertFalse(condition: $hasher->verify(password: 'wrong', hash: $hash));
    }

    public function testNeedsRehash() : void
    {
        $hasher = new PasswordHasher(options: ['cost' => 10]);
        $hash = $hasher->hash(password: 'secret');

        $this->assertFalse(condition: $hasher->needsRehash(hash: $hash));

        $newHasher = new PasswordHasher(options: ['cost' => 12]);
        $this->assertTrue(condition: $newHasher->needsRehash(hash: $hash));
    }
}
