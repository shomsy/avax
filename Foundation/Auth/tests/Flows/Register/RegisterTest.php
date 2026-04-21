<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Register;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use Exception;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Register flow.
 */
class RegisterTest extends TestCase
{
    /**
     * @throws Exception
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
        $userSource->shouldReceive('create')->once()->andReturnUsing(static fn ($user) => $user);

        $passwordHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);

        $idGenerator = new class implements IdGeneratorInterface {
            public function generate() : int
            {
                return 123456;
            }
        };

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog(),
            clock                   : new Clock()
        );

        $result = $register->execute(data: $data);

        $this->assertEquals(expected: 'new@example.com', actual: $result->user()->email);
        $this->assertEquals(expected: 123456, actual: $result->user()->id);
    }

    /**
     * @throws RateLimitException
     */
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

        $passwordHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $idGenerator    = new class implements IdGeneratorInterface {
            public function generate() : int
            {
                return 123456;
            }
        };

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog(),
            clock                   : new Clock()
        );

        $this->expectException(exception: RegistrationFailed::class);
        $this->expectExceptionMessage(message: 'Email is already taken.');
        $this->expectExceptionCode(code: 409);

        $register->execute(data: $data);
    }

    /**
     * @throws RateLimitException
     */
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

        $passwordHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $idGenerator    = new class implements IdGeneratorInterface {
            public function generate() : int
            {
                return 123456;
            }
        };

        $register = new Register(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            idGenerator             : $idGenerator,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            auditLog                : new InMemoryAuditLog(),
            clock                   : new Clock()
        );

        $this->expectException(exception: RegistrationFailed::class);
        $this->expectExceptionMessage(message: 'Username is already taken.');
        $this->expectExceptionCode(code: 409);

        $register->execute(data: $data);
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
