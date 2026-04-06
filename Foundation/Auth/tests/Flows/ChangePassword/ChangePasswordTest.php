<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\ChangePassword;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Mockery;
use Exception;

/**
 * Unit test for ChangePassword flow.
 */
class ChangePasswordTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * @throws \Exception
     */
    public function testChangePasswordSuccess() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');
        $user->shouldReceive('getId')->andReturn(new UserId(1));

        $data = new ChangePasswordData(
            currentPassword: 'old_password',
            newPassword: 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('old_password', 'old_hash')->andReturn(true);
        $passwordHasher->shouldReceive('hash')->with('new_password')->andReturn('new_hash');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('updatePassword')->once();

        $changePassword = new ChangePassword(
            userSource: $userSource,
            passwordHasher: $passwordHasher
        );

        $changePassword->execute(user: $user, data: $data);

        $this->assertTrue(true);
    }

    public function testChangePasswordFailureIncorrectCurrentPassword() : void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');

        $data = new ChangePasswordData(
            currentPassword: 'wrong_password',
            newPassword: 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('wrong_password', 'old_hash')->andReturn(false);

        $userSource = Mockery::mock(UserSourceInterface::class);

        $changePassword = new ChangePassword(
            userSource: $userSource,
            passwordHasher: $passwordHasher
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Current password is incorrect.');
        $this->expectExceptionCode(403);

        $changePassword->execute(user: $user, data: $data);
    }
}
