<?php

declare(strict_types=1);

namespace Avax\Components\Auth\Tests\Flows\CheckAuthentication;

use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Components\Tests\TestCase;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CheckAuthentication flow.
 */
class CheckAuthenticationTest extends TestCase
{
    public function testCheckAuthenticationSuccessSession() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'active@example.com', username: 'active'),
            mode: AuthenticationMode::SESSION
        ));

        $check = new CheckAuthentication(currentAuthentication: $currentAuthentication);
        $this->assertTrue(condition: $check->execute());
    }

    public function testCheckAuthenticationFailureSession() : void
    {
        $check = new CheckAuthentication(currentAuthentication: new CurrentAuthentication());
        $this->assertFalse(condition: $check->execute());
    }
}
