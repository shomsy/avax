<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Login;

use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AuthenticationStateTest extends TestCase
{
    #[Test]
    public function test_authenticated_state_exists(): void
    {
        self::assertSame('authenticated', AuthenticationState::AUTHENTICATED->value);
    }

    #[Test]
    public function test_mfa_required_state_exists(): void
    {
        self::assertSame('mfa_required', AuthenticationState::MFA_REQUIRED->value);
    }
}
