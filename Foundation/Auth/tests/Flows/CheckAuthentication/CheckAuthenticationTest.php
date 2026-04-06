<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\CheckAuthentication;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
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
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('check')->once()->andReturn(true);

        $check = new CheckAuthentication(identity: $identity);
        $this->assertTrue($check->execute());
    }

    public function testCheckAuthenticationFailureSession() : void
    {
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('check')->once()->andReturn(false);

        $check = new CheckAuthentication(identity: $identity);
        $this->assertFalse($check->execute());
    }
}
