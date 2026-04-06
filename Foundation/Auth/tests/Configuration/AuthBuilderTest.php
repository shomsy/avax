<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Mockery;
use RuntimeException;

/**
 * Unit test for AuthBuilder (Configuration slice).
 */
class AuthBuilderTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testAuthBuilderThrowsExceptionWithoutUserSource() : void
    {
        $builder = new AuthBuilder();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Data source is required (forUser).');
        $builder->ready();
    }

    public function testAuthBuilderThrowsExceptionWithoutIdentityProviders() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('At least one identity adapter is required.');
        $builder->ready();
    }

    public function testAuthBuilderBuildsAuthInstance() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $session = Mockery::mock(SessionIdentityInterface::class);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withSession(sessionIdentity: $session);

        $auth = $builder->ready();

        $this->assertInstanceOf(Auth::class, $auth);
    }
}
