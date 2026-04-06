<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
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

    public function testAuthBuilderThrowsExceptionWithoutIdentity() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Identity is required (withIdentity).');
        $builder->ready();
    }

    public function testAuthBuilderBuildsAuthInstance() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity = Mockery::mock(IdentityInterface::class);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity);

        $auth = $builder->ready();

        $this->assertInstanceOf(Auth::class, $auth);
    }
}
