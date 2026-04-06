<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Logout;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Mockery;

/**
 * Unit test for Logout flow.
 */
class LogoutTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testLogoutClearsIdentity() : void
    {
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();

        $logout = new Logout(identity: $identity);
        $logout->execute();

        $this->assertTrue(true);
    }
}
