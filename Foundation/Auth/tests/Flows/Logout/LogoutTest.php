<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Logout;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Flow\Logout\Logout;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Logout flow.
 */
class LogoutTest extends TestCase
{
    public function testLogoutClearsIdentity() : void
    {
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();

        $logout = new Logout(identity: $identity);
        $logout->execute();

        $this->assertTrue(condition: true);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
