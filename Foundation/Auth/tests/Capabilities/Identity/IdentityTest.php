<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Identity;

use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\Token\IssuedToken;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the unified Identity façade.
 */
class IdentityTest extends TestCase
{
    public function testIdentityRequiresAtLeastOneBackend() : void
    {
        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Identity requires at least one backend.');

        new Identity();
    }

    public function testIdentityRejectsInactiveUsersWhenIssuing() : void
    {
        $user = new User(
            id          : new UserId(value: 10),
            email       : new UserEmail(value: 'inactive@example.com'),
            username    : 'inactive',
            passwordHash: 'hash',
            isActive    : false
        );

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldNotReceive('issue');

        $identity = new Identity(jwtIdentity: $jwt);

        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Inactive users cannot be authenticated.');

        $identity->issue(user: $user);
    }

    public function testIdentityIssuesAllConfiguredBackends() : void
    {
        $user = new User(
            id          : new UserId(value: 10),
            email       : new UserEmail(value: 'identity@example.com'),
            username    : 'identity',
            passwordHash: 'hash'
        );

        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('issue')->once()->with(10, null, false)->andReturn('session-10');

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('issue')->once()->with($user, null, false)->andReturn(new IssuedToken(
                                                                               token    : 'token-10',
                                                                               tokenId  : 'token-id',
                                                                               expiresAt: new \DateTimeImmutable('+1 hour')
                                                                           ));
        $jwt->shouldReceive('issueRefreshToken')->once()->with($user, null, false)->andReturn(null);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $issued = $identity->issue(user: $user);

        $this->assertSame(AuthenticationMode::HYBRID, $issued->mode);
        $this->assertSame('session-10', $issued->sessionId);
        $this->assertSame('token-10', $issued->accessToken?->token);
    }

    public function testIdentityClearsAllConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('clear')->once();

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('revoke')->once()->with('token-10', Mockery::type(\DateTimeImmutable::class));

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $identity->clear(AuthenticationContext::authenticated(
            user                : new \Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser(id: 10, email: 'identity@example.com', username: 'identity'),
            mode                : AuthenticationMode::TOKEN,
            accessTokenId       : 'token-10',
            accessTokenExpiresAt: new \DateTimeImmutable('+1 hour')
        ));

        $this->assertTrue(condition: true);
    }

    public function testIdentityExposesConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $jwt     = Mockery::mock(JwtIdentityInterface::class);

        $identity = new Identity(sessionIdentity: $session, jwtIdentity: $jwt);

        $this->assertSame($session, $identity->sessionIdentity());
        $this->assertSame($jwt, $identity->jwtIdentity());
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
