<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\Identity;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Tests\TestCase;
use DateTimeImmutable;
use InvalidArgumentException;
use Mockery;
use Override;
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
        $jwt->shouldReceive('issue')->once()->with($user, null, false, null, [], null)->andReturn(new IssuedToken(
                                                                                                      token    : 'token-10',
                                                                                                      tokenId  : 'token-id',
                                                                                                      expiresAt: new DateTimeImmutable(datetime: '+1 hour')
                                                                                                  ));
        $jwt->shouldReceive('issueRefreshToken')->once()->with($user, null, false)->andReturn(null);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $issued = $identity->issue(user: $user);

        $this->assertSame(expected: AuthenticationMode::HYBRID, actual: $issued->mode);
        $this->assertSame(expected: 'session-10', actual: $issued->sessionId);
        $this->assertSame(expected: 'token-10', actual: $issued->accessToken?->token);
    }

    public function testIdentityUsesSessionModeWhenOnlySessionBackendIsConfigured() : void
    {
        $user = new User(
            id          : new UserId(value: 11),
            email       : new UserEmail(value: 'session-only@example.com'),
            username    : 'session-only',
            passwordHash: 'hash'
        );

        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('issue')->once()->with(11, null, false)->andReturn('session-11');

        $identity = new Identity(sessionIdentity: $session);

        $issued = $identity->issue(user: $user);

        $this->assertSame(expected: AuthenticationMode::SESSION, actual: $issued->mode);
        $this->assertSame(expected: 'session-11', actual: $issued->sessionId);
        $this->assertNull(actual: $issued->accessToken);
        $this->assertNull(actual: $issued->refreshToken);
    }

    public function testIdentityUsesTokenModeWhenOnlyJwtBackendIsConfigured() : void
    {
        $user = new User(
            id          : new UserId(value: 12),
            email       : new UserEmail(value: 'token-only@example.com'),
            username    : 'token-only',
            passwordHash: 'hash'
        );

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('issueRefreshToken')
            ->once()
            ->withAnyArgs()
            ->andReturn(null);
        $jwt->shouldReceive('issue')
            ->once()
            ->withAnyArgs()
            ->andReturn(new IssuedToken(
                            token    : 'token-12',
                            tokenId  : 'token-id-12',
                            expiresAt: new DateTimeImmutable(datetime: '+1 hour')
                        ));

        $identity = new Identity(jwtIdentity: $jwt);

        $issued = $identity->issue(user: $user);

        $this->assertSame(expected: AuthenticationMode::TOKEN, actual: $issued->mode);
        $this->assertNull(actual: $issued->sessionId);
        $this->assertSame(expected: 'token-12', actual: $issued->accessToken?->token);
    }

    public function testIdentityClearsAllConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('clear')->once();

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('revoke')->once()->with('token-10', Mockery::type(expected: DateTimeImmutable::class));

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $identity->clear(context: AuthenticationContext::authenticated(
            user                : new AuthenticatedUser(id: 10, email: 'identity@example.com', username: 'identity'),
            mode                : AuthenticationMode::TOKEN,
            accessTokenId       : 'token-10',
            accessTokenExpiresAt: new DateTimeImmutable(datetime: '+1 hour')
        ));

        $this->assertTrue(condition: true);
    }

    public function testIdentityClearsSessionBackendWithoutJwtContext() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('clear')->once();

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldNotReceive('revoke');

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $identity->clear();

        $this->assertTrue(condition: true);
    }

    public function testIdentityExposesConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $jwt     = Mockery::mock(JwtIdentityInterface::class);

        $identity = new Identity(sessionIdentity: $session, jwtIdentity: $jwt);

        $this->assertSame(expected: $session, actual: $identity->sessionIdentity());
        $this->assertSame(expected: $jwt, actual: $identity->jwtIdentity());
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
