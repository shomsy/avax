<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Register;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Register\RegistrationFailed;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Register flow.
 */
class RegisterTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testRegisterSuccess() : void
    {
        $data = new RegistrationData(
            email   : 'new@example.com',
            username: 'newuser',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(false);
        $userSource->shouldReceive('create')->once()->andReturnUsing(fn ($user) => $user);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('hash')->with('password')->andReturn('hashed_password');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldReceive('generate')->andReturn(123456);

        $register = new Register(
            userSource    : $userSource,
            passwordHasher: $passwordHasher,
            idGenerator   : $idGenerator
        );

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog()
        );

        $result = $register->execute(data: $data);

        $this->assertEquals(expected: 'new@example.com', actual: $result->user()->email);
        $this->assertEquals(expected: 123456, actual: $result->user()->id);
    }

    public function testRegisterFailureEmailTaken() : void
    {
        $data = new RegistrationData(
            email   : 'taken@example.com',
            username: 'user',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(true);
        $userSource->shouldNotReceive('usernameExists');

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $idGenerator    = Mockery::mock(IdGeneratorInterface::class);

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog()
        );

        $this->expectException(exception: RegistrationFailed::class);
        $this->expectExceptionMessage(message: 'Email is already taken.');
        $this->expectExceptionCode(code: 409);

        $register->execute(data: $data);
    }

    public function testRegisterFailureUsernameTaken() : void
    {
        $data = new RegistrationData(
            email   : 'taken-username@example.com',
            username: 'taken-user',
            password: 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('emailExists')->with($data->email)->andReturn(false);
        $userSource->shouldReceive('usernameExists')->with($data->username)->andReturn(true);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $idGenerator    = Mockery::mock(IdGeneratorInterface::class);

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog()
        );

        $this->expectException(exception: RegistrationFailed::class);
        $this->expectExceptionMessage(message: 'Username is already taken.');
        $this->expectExceptionCode(code: 409);

        $register->execute(data: $data);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
