<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Logout;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Logout flow.
 */
class LogoutTest extends TestCase
{
    public function test_logout_clears_identity() : void
    {
        $currentAuthentication = new CurrentAuthentication;
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
            auditLog             : new InMemoryAuditLog,
            clock                : new Clock,
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
