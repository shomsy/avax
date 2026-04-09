<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Logout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Logout flow.
 */
class LogoutTest extends TestCase
{
    public function testLogoutClearsIdentity() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 7, email: 'logout@example.com', username: 'logout'),
            mode: AuthenticationMode::TOKEN
        ));
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();
        $refreshTokenStore = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokenStore->shouldReceive('revokeUser')->once();

        $logout = new Logout(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            refreshTokenStore    : $refreshTokenStore
        );
        $logout->execute();

        $this->assertTrue(condition: true);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
