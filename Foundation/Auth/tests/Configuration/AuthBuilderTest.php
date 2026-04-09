<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Configuration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit test for AuthBuilder (Configuration slice).
 */
class AuthBuilderTest extends TestCase
{
    public function testAuthBuilderThrowsExceptionWithoutUserSource() : void
    {
        $builder = new AuthBuilder();
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Data source is required (forUser).');
        $builder->ready();
    }

    public function testAuthBuilderThrowsExceptionWithoutIdentity() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $builder    = new AuthBuilder();
        $builder->forUser(userSource: $userSource);

        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Identity is required (withIdentity).');
        $builder->ready();
    }

    public function testAuthBuilderBuildsAuthInstance() : void
    {
        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);

        $builder = new AuthBuilder();
        $builder->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity);

        $auth = $builder->ready();

        $this->assertInstanceOf(expected: Auth::class, actual: $auth);
    }

    /**
     * @throws \Exception
     */
    public function testAuthBuilderUsesConfiguredIdGenerator() : void
    {
        $data = new RegistrationData(
            email   : 'builder@example.com',
            username: 'builder',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(false);
        $userSource->shouldReceive('create')->once()->andReturnUsing(fn ($user) => $user);

        $identity = Mockery::mock(IdentityInterface::class);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldReceive('generate')->once()->andReturn(987654);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('hash')->with('password')->andReturn('hashed_password');

        $builder = new AuthBuilder();
        $auth    = $builder
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->usingIdGenerator(idGenerator: $idGenerator)
            ->usingHasher(passwordHasher: $passwordHasher)
            ->ready();

        $user = $auth->register(data: $data);

        $this->assertSame(expected: 987654, actual: $user->getId()->value);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
