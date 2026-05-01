<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\System;

use Avax\Components\Identity\Auth\System\Foundation\Text\NormalizeEmail;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use PHPUnit\Framework\TestCase;

final class AuthFoundationSmokeTest extends TestCase
{
    public function test_password_hashing_round_trip_verifies_password(): void
    {
        $passwordHasher = new PasswordHasher();

        $hash = $passwordHasher->hash(password: 'correct horse battery staple');

        self::assertTrue($passwordHasher->verify(password: 'correct horse battery staple', hash: $hash));
        self::assertFalse($passwordHasher->verify(password: 'wrong password', hash: $hash));
    }

    public function test_email_normalization_trims_and_lowercases(): void
    {
        $normalizeEmail = new NormalizeEmail();

        self::assertSame(
            expected: 'user@example.com',
            actual  : $normalizeEmail(value: '  USER@Example.COM  '),
        );
    }
}
