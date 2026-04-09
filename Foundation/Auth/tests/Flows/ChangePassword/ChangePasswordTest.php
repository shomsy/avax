<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\ChangePassword;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ChangePassword flow.
 */
class ChangePasswordTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testChangePasswordSuccess() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');
        $user->shouldReceive('getId')->andReturn(new UserId(value: 1));

        $data = new ChangePasswordData(
            currentPassword: 'old_password',
            newPassword    : 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('old_password', 'old_hash')->andReturn(true);
        $passwordHasher->shouldReceive('hash')->with('new_password')->andReturn('new_hash');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('updatePassword')->once();

        $changePassword = new ChangePassword(
            userSource    : $userSource,
            passwordHasher: $passwordHasher
        );

        $changePassword->execute(user: $user, data: $data);

        $this->assertTrue(condition: true);
    }

    public function testChangePasswordFailureIncorrectCurrentPassword() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');

        $data = new ChangePasswordData(
            currentPassword: 'wrong_password',
            newPassword    : 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('wrong_password', 'old_hash')->andReturn(false);

        $userSource = Mockery::mock(UserSourceInterface::class);

        $changePassword = new ChangePassword(
            userSource    : $userSource,
            passwordHasher: $passwordHasher
        );

        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Current password is incorrect.');
        $this->expectExceptionCode(code: 403);

        $changePassword->execute(user: $user, data: $data);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
