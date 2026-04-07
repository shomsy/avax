<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\CheckAuthentication;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Mockery;

/**
 * Unit test for CheckAuthentication flow.
 */
class CheckAuthenticationTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testCheckAuthenticationSuccessSession() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->once()->andReturn(
            new User(
                id: new UserId(1),
                email: new UserEmail('active@example.com'),
                username: 'active',
                passwordHash: 'hash'
            )
        );

        $check = new CheckAuthentication(readCurrentUser: $readCurrentUser);
        $this->assertTrue($check->execute());
    }

    public function testCheckAuthenticationFailureSession() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->once()->andReturn(null);

        $check = new CheckAuthentication(readCurrentUser: $readCurrentUser);
        $this->assertFalse($check->execute());
    }
}
