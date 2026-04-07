<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Identity\Jwt;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use InvalidArgumentException;
use Mockery;

/**
 * Unit test for JWT identity state handling.
 */
class JwtIdentityTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testJwtIdentityIssueAuthenticateAndClearCycle() : void
    {
        $user = new User(
            id: new UserId(7),
            email: new UserEmail('jwt@example.com'),
            username: 'jwt-user',
            passwordHash: 'hash'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->with(Mockery::on(fn($id) => $id instanceof UserId && $id->value === 7))
            ->andReturn($user);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            secret: 'super-secret-key'
        );

        $token = $jwt->issue($user);

        $this->assertIsString($token);
        $this->assertSame($token, $jwt->token());
        $this->assertTrue($jwt->check());
        $this->assertSame($user, $jwt->getCurrentUser());

        $jwt->clear();

        $this->assertFalse($jwt->check());
        $this->assertNull($jwt->getCurrentUser());
        $this->assertNull($jwt->token());

        $jwt->authenticate($token);

        $this->assertTrue($jwt->check());
        $this->assertSame($user, $jwt->getCurrentUser());
        $this->assertSame($token, $jwt->token());
    }

    public function testJwtIdentityRejectsInactiveUsersWhenIssuing() : void
    {
        $inactiveUser = new User(
            id: new UserId(8),
            email: new UserEmail('inactive-issue@example.com'),
            username: 'inactive-issue',
            passwordHash: 'hash',
            isActive: false
        );

        $jwt = new JwtIdentity(
            userSource: Mockery::mock(UserSourceInterface::class),
            secret: 'super-secret-key'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Inactive users cannot be authenticated.');

        $jwt->issue($inactiveUser);
    }

    public function testJwtIdentityRejectsInactiveUserTokens() : void
    {
        $activeUser = new User(
            id: new UserId(9),
            email: new UserEmail('active-jwt@example.com'),
            username: 'active-jwt',
            passwordHash: 'hash'
        );

        $inactiveUser = new User(
            id: new UserId(9),
            email: new UserEmail('inactive-jwt@example.com'),
            username: 'inactive-jwt',
            passwordHash: 'hash',
            isActive: false
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->with(Mockery::on(fn($id) => $id instanceof UserId && $id->value === 9))
            ->andReturn($inactiveUser);

        $jwt = new JwtIdentity(
            userSource: $userSource,
            secret: 'super-secret-key'
        );

        $token = $jwt->issue($activeUser);

        $jwt->authenticate($token);

        $this->assertFalse($jwt->check());
        $this->assertNull($jwt->getCurrentUser());
        $this->assertNull($jwt->token());
    }
}
