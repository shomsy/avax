<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Identity;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Mockery;

/**
 * Unit test for the unified Identity façade.
 */
class IdentityTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testIdentityIssuesAllConfiguredBackends() : void
    {
        $user = new User(
            id: new UserId(10),
            email: new UserEmail('identity@example.com'),
            username: 'identity',
            passwordHash: 'hash'
        );

        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('issue')->once()->with(10);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('issue')->once()->with($user)->andReturn('token-10');

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity: $jwt
        );

        $this->assertSame('token-10', $identity->issue($user));
    }

    public function testIdentityCheckReturnsTrueWhenAnyBackendIsAuthenticated() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('check')->once()->andReturn(false);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('check')->once()->andReturn(true);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity: $jwt
        );

        $this->assertTrue($identity->check());
    }

    public function testIdentityResolvesCurrentUserAndUserId() : void
    {
        $user = new User(
            id: new UserId(25),
            email: new UserEmail('current@example.com'),
            username: 'current',
            passwordHash: 'hash'
        );

        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('getUserId')->once()->andReturn(25);

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('getCurrentUser')->once()->andReturn($user);

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity: $jwt
        );

        $this->assertSame($user, $identity->getCurrentUser());
        $this->assertSame(25, $identity->getUserId());
    }

    public function testIdentityClearsAllConfiguredBackends() : void
    {
        $session = Mockery::mock(SessionIdentityInterface::class);
        $session->shouldReceive('clear')->once();

        $jwt = Mockery::mock(JwtIdentityInterface::class);
        $jwt->shouldReceive('clear')->once();

        $identity = new Identity(
            sessionIdentity: $session,
            jwtIdentity: $jwt
        );

        $identity->clear();

        $this->assertTrue(true);
    }
}
