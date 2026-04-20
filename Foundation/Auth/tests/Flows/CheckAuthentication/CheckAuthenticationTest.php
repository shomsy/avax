<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\CheckAuthentication;

use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
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
