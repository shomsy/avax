<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Register;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Mockery;
use Exception;

/**
 * Unit test for Register flow.
 */
class RegisterTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * @throws \Exception
     */
    public function testRegisterSuccess() : void
    {
        $data = new RegistrationData(
            email: 'new@example.com',
            username: 'newuser',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(false);
        $userSource->shouldReceive('create')->once()->andReturnUsing(fn($user) => $user);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('hash')->with('password')->andReturn('hashed_password');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldReceive('generate')->andReturn(123456);

        $register = new Register(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            idGenerator: $idGenerator
        );

        $user = $register->execute(data: $data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('new@example.com', $user->getEmail()->value);
        $this->assertEquals(123456, $user->getId()->value);
    }

    public function testRegisterFailureEmailTaken() : void
    {
        $data = new RegistrationData(
            email: 'taken@example.com',
            username: 'user',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(true);
        $userSource->shouldNotReceive('usernameExists');

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $register = new Register(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            idGenerator: $idGenerator
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email is already taken.');
        $this->expectExceptionCode(409);

        $register->execute(data: $data);
    }

    public function testRegisterFailureUsernameTaken() : void
    {
        $data = new RegistrationData(
            email: 'taken-username@example.com',
            username: 'taken-user',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(true);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $register = new Register(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            idGenerator: $idGenerator
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Username is already taken.');
        $this->expectExceptionCode(409);

        $register->execute(data: $data);
    }
}
