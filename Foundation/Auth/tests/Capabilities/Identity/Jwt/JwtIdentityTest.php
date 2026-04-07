<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Identity\Jwt;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
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
            id: new UserId(value: 7),
            email: new UserEmail(value: 'jwt@example.com'),
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

        $token = $jwt->issue(user: $user);

        $this->assertIsString(actual: $token);
        $this->assertSame(expected: $token, actual: $jwt->token());
        $this->assertTrue(condition: $jwt->check());
        $this->assertSame(expected: $user, actual: $jwt->getCurrentUser());

        $jwt->clear();

        $this->assertFalse(condition: $jwt->check());
        $this->assertNull(actual: $jwt->getCurrentUser());
        $this->assertNull(actual: $jwt->token());

        $jwt->authenticate(token: $token);

        $this->assertTrue(condition: $jwt->check());
        $this->assertSame(expected: $user, actual: $jwt->getCurrentUser());
        $this->assertSame(expected: $token, actual: $jwt->token());
    }

    public function testJwtIdentityRejectsInactiveUsersWhenIssuing() : void
    {
        $inactiveUser = new User(
            id: new UserId(value: 8),
            email: new UserEmail(value: 'inactive-issue@example.com'),
            username: 'inactive-issue',
            passwordHash: 'hash',
            isActive: false
        );

        $jwt = new JwtIdentity(
            userSource: Mockery::mock(UserSourceInterface::class),
            secret: 'super-secret-key'
        );

        $this->expectException(exception: InvalidArgumentException::class);
        $this->expectExceptionMessage(message: 'Inactive users cannot be authenticated.');

        $jwt->issue(user: $inactiveUser);
    }

    public function testJwtIdentityRejectsInactiveUserTokens() : void
    {
        $activeUser = new User(
            id: new UserId(value: 9),
            email: new UserEmail(value: 'active-jwt@example.com'),
            username: 'active-jwt',
            passwordHash: 'hash'
        );

        $inactiveUser = new User(
            id: new UserId(value: 9),
            email: new UserEmail(value: 'inactive-jwt@example.com'),
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

        $token = $jwt->issue(user: $activeUser);

        $jwt->authenticate(token: $token);

        $this->assertFalse(condition: $jwt->check());
        $this->assertNull(actual: $jwt->getCurrentUser());
        $this->assertNull(actual: $jwt->token());
    }
}
