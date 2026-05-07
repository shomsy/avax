<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth;

use Avax\Components\Identity\Auth\System\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\TestCase;

final class AuthCapabilitiesTest extends TestCase
{
    public function test_password_hasher_verifies_hashes() : void
    {
        $hasher = new PasswordHasher();
        $hash   = $hasher->hash('secret');

        $this->assertTrue($hasher->verify('secret', $hash));
        $this->assertFalse($hasher->verify('wrong', $hash));
    }
}
