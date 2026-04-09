<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\ChangePassword;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ChangePassword flow.
 */
class ChangePasswordTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testChangePasswordSuccess() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::TOKEN
        ));
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');
        $user->shouldReceive('getId')->andReturn(new UserId(value: 1));

        $data = new ChangePasswordData(
            currentPassword: 'old_password',
            newPassword    : 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('old_password', 'old_hash')->andReturn(true);
        $passwordHasher->shouldReceive('hash')->with('new_password')->andReturn('new_hash');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturn($user);
        $userSource->shouldReceive('updatePassword')->once();

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();
        $refreshTokenStore = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokenStore->shouldReceive('revokeUser')->once();

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            refreshTokenStore    : $refreshTokenStore
        );

        $changePassword->execute(data: $data);

        $this->assertTrue(condition: true);
    }

    public function testChangePasswordFailureIncorrectCurrentPassword() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::TOKEN
        ));
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getPasswordHash')->andReturn('old_hash');
        $user->shouldReceive('isActive')->andReturn(true);

        $data = new ChangePasswordData(
            currentPassword: 'wrong_password',
            newPassword    : 'new_password'
        );

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->with('wrong_password', 'old_hash')->andReturn(false);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')->once()->andReturn($user);

        $changePassword = new ChangePassword(
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog()
        );

        $this->expectException(exception: PasswordChangeFailed::class);
        $this->expectExceptionMessage(message: 'Current password is incorrect.');
        $this->expectExceptionCode(code: 403);

        $changePassword->execute(data: $data);
    }

    public function testChangePasswordRequiresAuthenticatedContext() : void
    {
        $changePassword = new ChangePassword(
            userSource           : Mockery::mock(UserSourceInterface::class),
            passwordHasher       : Mockery::mock(PasswordHasher::class),
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: new CurrentAuthentication(),
            auditLog             : new InMemoryAuditLog()
        );

        $this->expectException(Unauthenticated::class);
        $changePassword->execute(new ChangePasswordData('old', 'new'));
    }

    public function testChangePasswordRequiresFreshMfaWhenUserHasMfaEnabled() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id        : 1,
                      email     : 'user@example.com',
                      username  : 'user',
                      mfaEnabled: true
                  ),
            mode: AuthenticationMode::TOKEN
        ));

        $changePassword = new ChangePassword(
            userSource           : Mockery::mock(UserSourceInterface::class),
            passwordHasher       : Mockery::mock(PasswordHasher::class),
            identity             : Mockery::mock(IdentityInterface::class),
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            requireFreshMfa      : new RequireFreshMfa(
                                       currentAuthentication: $currentAuthentication,
                                       clock                : new Clock()
                                   )
        );

        $this->expectException(FreshMfaRequired::class);
        $changePassword->execute(new ChangePasswordData('old', 'new'));
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
