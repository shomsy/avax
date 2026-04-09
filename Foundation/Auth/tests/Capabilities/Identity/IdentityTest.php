<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Identity;

use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
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
        $session->shouldReceive('issue')->once()->with(10);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('issue')->once()->with($user)->andReturn('token-10');

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $this->assertSame(expected: 'token-10', actual: $identity->issue(user: $user));
    }

    public function testIdentityCheckReturnsTrueWhenAnyBackendIsAuthenticated() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('check')->once()->andReturn(false);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('check')->once()->andReturn(true);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $this->assertTrue(condition: $identity->check());
    }

    public function testIdentityResolvesCurrentUserAndUserId() : void
    {
        $user = new User(
            id          : new UserId(value: 25),
            email       : new UserEmail(value: 'current@example.com'),
            username    : 'current',
            passwordHash: 'hash'
        );

        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('getUserId')->once()->andReturn(25);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('getCurrentUser')->once()->andReturn($user);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $this->assertSame(expected: $user, actual: $identity->getCurrentUser());
        $this->assertSame(expected: 25, actual: $identity->getUserId());
    }

    public function testIdentityClearsAllConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('clear')->once();

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('clear')->once();

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity    : $jwt
        );

        $identity->clear();

        $this->assertTrue(condition: true);
    }

    public function testIdentityAuthenticatesJwtAndReturnsToken() : void
    {
        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('authenticate')->once()->with('token-42');
        $jwt->shouldReceive('token')->once()->andReturn('token-42');

        $identity = new Identity(jwtIdentity: $jwt);

        $identity->authenticate(token: 'token-42');

        $this->assertSame(expected: 'token-42', actual: $identity->token());
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
