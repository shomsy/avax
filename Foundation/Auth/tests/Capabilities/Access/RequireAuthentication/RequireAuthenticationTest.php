<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequireAuthentication;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Mockery;

/**
 * Unit test for RequireAuthentication access boundary.
 */
class RequireAuthenticationTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testRequireAuthenticationSuccess() : void
    {
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('execute')->andReturn(true);

        $requirement = new RequireAuthentication(checkAuthentication: $checkAuthentication);
        $requirement->execute();

        $this->assertTrue(condition: true); // No exception thrown
    }

    public function testRequireAuthenticationFailure() : void
    {
        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('execute')->andReturn(false);

        $requirement = new RequireAuthentication(checkAuthentication: $checkAuthentication);

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute();
    }
}
