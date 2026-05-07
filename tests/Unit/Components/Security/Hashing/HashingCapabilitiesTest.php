<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Hashing;

use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\TestCase;

final class HashingCapabilitiesTest extends TestCase
{
    public function test_it_hashes_password_and_verifies_correctly() : void
    {
        $hasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $hash   = $hasher->hash('StrongPassword123');
        $this->assertTrue($hasher->verify('StrongPassword123', $hash));
    }

    public function test_it_rejects_wrong_password() : void
    {
        $hasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $hash   = $hasher->hash('CorrectPassword');
        $this->assertFalse($hasher->verify('WrongPassword', $hash));
    }

    public function test_it_detects_rehash_need_when_options_change() : void
    {
        $hasherLow = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $hash      = $hasherLow->hash('password');

        $hasherHigh = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 10]);
        $this->assertTrue($hasherHigh->needsRehash($hash));
    }

    public function test_it_does_not_need_rehash_when_options_match() : void
    {
        $hasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $hash   = $hasher->hash('password');
        $this->assertFalse($hasher->needsRehash($hash));
    }

    public function test_it_provides_dummy_hash_for_timing_attack_mitigation() : void
    {
        $hasher = new PasswordHasher();
        $dummy  = $hasher->dummyHash();
        $this->assertNotEmpty($dummy);
        $this->assertStringStartsWith('$2y$', $dummy);
    }

    public function test_it_produces_different_hashes_for_same_password() : void
    {
        $hasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $hash1  = $hasher->hash('SamePassword');
        $hash2  = $hasher->hash('SamePassword');
        $this->assertNotSame($hash1, $hash2);
    }
}
