<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\CheckAuthentication;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CheckAuthentication flow.
 */
class CheckAuthenticationTest extends TestCase
{
    public function testCheckAuthenticationSuccessSession() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->once()->andReturn(
            new User(
                id          : new UserId(value: 1),
                email       : new UserEmail(value: 'active@example.com'),
                username    : 'active',
                passwordHash: 'hash'
            )
        );

        $check = new CheckAuthentication(readCurrentUser: $readCurrentUser);
        $this->assertTrue(condition: $check->execute());
    }

    public function testCheckAuthenticationFailureSession() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->once()->andReturn(null);

        $check = new CheckAuthentication(readCurrentUser: $readCurrentUser);
        $this->assertFalse(condition: $check->execute());
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
