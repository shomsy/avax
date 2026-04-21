<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\ChangePassword;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Foundation\Clock;
use Exception;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

/**
 * Unit test for ChangePassword flow.
 */
class ChangePasswordTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testChangePasswordSuccess() : void
    {
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $context               = AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::TOKEN
        );
        $currentAuthentication->store(context: $context);
        $user = $this->userWithPassword(passwordHasher: $passwordHasher, password: 'old_password');

        $data = new ChangePasswordData(
            currentPassword: 'old_password',
            newPassword    : 'new_password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturn($user);
        $userSource->shouldReceive('updatePassword')->once()->withArgs(argsOrClosure: function (UserId $id, #[SensitiveParameter] string $passwordHash) use ($user, $passwordHasher) {
            return $id->value === $user->getId()->value
                && $passwordHash !== $user->getPasswordHash()
                && $passwordHasher->verify(password: 'new_password', hash: $passwordHash);
        });

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);
        $refreshTokenStore = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokenStore->shouldReceive('revokeUser')->once()->with($user->getId());

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock(),
            refreshTokenStore    : $refreshTokenStore
        );

        $changePassword->execute(data: $data);

        $this->assertTrue(condition: true);
    }

    private function passwordHasher() : PasswordHasher
    {
        return new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
    }

    private function userWithPassword(#[SensitiveParameter] PasswordHasher $passwordHasher, #[SensitiveParameter] string $password) : User
    {
        return User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: "user1@example.com"),
            username    : "user1",
            passwordHash: $passwordHasher->hash(password: $password),
            isActive    : true
        );
    }

    /**
     * @throws RateLimitException
     * @throws Unauthenticated
     */
    public function testChangePasswordFailureIncorrectCurrentPassword() : void
    {
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::TOKEN
        ));
        $user = $this->userWithPassword(passwordHasher: $passwordHasher, password: 'old_password');

        $data = new ChangePasswordData(
            currentPassword: 'wrong_password',
            newPassword    : 'new_password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturn($user);

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock()
        );

        $this->expectException(exception: PasswordChangeFailed::class);
        $this->expectExceptionMessage(message: 'Current password is incorrect.');
        $this->expectExceptionCode(code: 403);

        $changePassword->execute(data: $data);
    }

    /**
     * @throws RateLimitException
     * @throws PasswordChangeFailed
     */
    public function testChangePasswordRequiresAuthenticatedContext() : void
    {
        $changePassword = new ChangePassword(
            userSource           : Mockery::mock(UserSourceInterface::class),
            passwordHasher       : $this->passwordHasher(),
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: new CurrentAuthentication(),
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock()
        );

        $this->expectException(Unauthenticated::class);
        $changePassword->execute(data: new ChangePasswordData(currentPassword: 'old', newPassword: 'new'));
    }

    /**
     * @throws Unauthenticated
     * @throws RateLimitException
     * @throws PasswordChangeFailed
     */
    public function testChangePasswordRequiresFreshMfaWhenUserHasMfaEnabled() : void
    {
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id        : 1,
                      email     : 'user@example.com',
                      username  : 'user',
                      mfaEnabled: true
                  ),
            mode: AuthenticationMode::TOKEN
        ));
        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturn(
            $this->userWithPassword(passwordHasher: $passwordHasher, password: 'old')
        );

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock(),
            requireFreshMfa      : new RequireFreshMfa(
                                       currentAuthentication: $currentAuthentication,
                                       clock                : new Clock()
                                   )
        );

        $this->expectException(FreshMfaRequired::class);
        $changePassword->execute(data: new ChangePasswordData(currentPassword: 'old', newPassword: 'new'));
    }

    /**
     * @throws RateLimitException
     * @throws PasswordChangeFailed
     */
    public function testChangePasswordRejectsStaleAuthenticatedUserSnapshot() : void
    {
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'stale@example.com', username: 'stale'),
            mode: AuthenticationMode::TOKEN
        ));

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturnNull();

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('clear');

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock()
        );

        $this->expectException(exception: Unauthenticated::class);
        $changePassword->execute(data: new ChangePasswordData(currentPassword: 'old', newPassword: 'new'));
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
