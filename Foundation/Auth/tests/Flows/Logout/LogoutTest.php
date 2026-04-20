<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Logout;

use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
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
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user                : new AuthenticatedUser(id: 7, email: 'logout@example.com', username: 'logout'),
            mode                : AuthenticationMode::TOKEN,
            refreshTokenFamilyId: 'fid-123'
        ));
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();
        $refreshTokenStore = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokenStore->shouldReceive('revokeFamily')->with('fid-123')->once();

        $logout = new Logout(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock(),
            refreshTokenStore    : $refreshTokenStore
        );
        $logout->execute();

        $this->assertTrue(condition: true);
    }

    #[\Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
