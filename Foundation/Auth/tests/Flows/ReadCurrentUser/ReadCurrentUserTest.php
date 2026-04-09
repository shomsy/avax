<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\ReadCurrentUser;

use Avax\Auth\System\Capability\User\UserEmail;
use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Mockery;

/**
 * Unit test for ReadCurrentUser flow.
 */
class ReadCurrentUserTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testReadCurrentUserFromSessionSuccess() : void
    {
        $userId = 123;
        $user = Mockery::mock(User::class);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('getCurrentUser')->andReturn(null);
        $identity->shouldReceive('getUserId')->andReturn($userId);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(fn($id) => $id instanceof UserId && $id->value === $userId))
            ->andReturn($user);

        $readCurrentUser = new ReadCurrentUser(
            identity: $identity,
            userSource: $userSource
        );

        $result = $readCurrentUser->execute();

        $this->assertSame(expected: $user, actual: $result);
    }

    public function testReadCurrentUserFromJwtSuccess() : void
    {
        $user = Mockery::mock(User::class);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('getCurrentUser')->andReturn($user);

        $userSource = Mockery::mock(UserSourceInterface::class);

        $readCurrentUser = new ReadCurrentUser(
            identity: $identity,
            userSource: $userSource
        );

        $result = $readCurrentUser->execute();

        $this->assertSame(expected: $user, actual: $result);
    }

    public function testReadCurrentUserReturnsNullWhenNoSessionFound() : void
    {
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('getCurrentUser')->andReturn(null);
        $identity->shouldReceive('getUserId')->andReturn(null);

        $userSource = Mockery::mock(UserSourceInterface::class);

        $readCurrentUser = new ReadCurrentUser(
            identity: $identity,
            userSource: $userSource
        );

        $result = $readCurrentUser->execute();

        $this->assertNull(actual: $result);
    }

    public function testReadCurrentUserReturnsNullForInactiveUser() : void
    {
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('getCurrentUser')->andReturn(null);
        $identity->shouldReceive('getUserId')->andReturn(555);

        $inactiveUser = new User(
            id: new UserId(value: 555),
            email: new UserEmail(value: 'inactive@example.com'),
            username: 'inactive',
            passwordHash: 'hash',
            isActive: false
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(fn($id) => $id instanceof UserId && $id->value === 555))
            ->andReturn($inactiveUser);

        $readCurrentUser = new ReadCurrentUser(
            identity: $identity,
            userSource: $userSource
        );

        $result = $readCurrentUser->execute();

        $this->assertNull(actual: $result);
    }
}
