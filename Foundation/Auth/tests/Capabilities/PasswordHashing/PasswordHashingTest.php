<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\PasswordHashing;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;

/**
 * Unit test for PasswordHashing capability.
 */
class PasswordHashingTest extends TestCase
{
    public function testHashAndVerify() : void
    {
        $hasher = new PasswordHasher();
        $password = 'secret';
        $hash = $hasher->hash($password);

        $this->assertNotSame($password, $hash);
        $this->assertTrue($hasher->verify($password, $hash));
        $this->assertFalse($hasher->verify('wrong', $hash));
    }

    public function testNeedsRehash() : void
    {
        $hasher = new PasswordHasher(options: ['cost' => 10]);
        $hash = $hasher->hash('secret');

        $this->assertFalse($hasher->needsRehash($hash));

        $newHasher = new PasswordHasher(options: ['cost' => 12]);
        $this->assertTrue($newHasher->needsRehash($hash));
    }
}
