<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Totp;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flow\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flow\Recover\ResetPasswordData;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Token\HmacTokenCodec;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flow\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flow\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

/**
 * Integration test covering the full user lifecycle.
 *
 * Banal: Testing the whole system together.
 */
class AuthLifecycleTest extends TestCase
{
    private Auth               $auth;
    private InMemoryUserSource $userSource;

    /**
     * @throws \Exception
     */
    public function testFullUserLifecyclePositive() : void
    {
        // 1. Register
        $regData      = new RegistrationData(
            email   : 'integration@test.com',
            username: 'itest',
            password: 'initial-password'
        );
        $registration = $this->auth->register(data: $regData);
        $this->assertEquals(expected: 'integration@test.com', actual: $registration->user()->email);

        // 2. Login
        $credentials = new Credentials(identifier: 'integration@test.com', password: 'initial-password');
        $loggedIn    = $this->auth->login(credentials: $credentials);
        $this->assertTrue($loggedIn->isAuthenticated());
        $this->assertSame('integration@test.com', $loggedIn->user()?->email);
        $this->assertNotNull($loggedIn->accessToken());
        $this->assertNotNull($loggedIn->refreshToken());

        $resolved = $this->auth->authenticateRequest(AuthenticationRequest::bearer($loggedIn->accessToken()));
        $this->assertTrue($resolved->isAuthenticated());

        // 3. Change Password
        $cpData = new ChangePasswordData(
            currentPassword: 'initial-password',
            newPassword    : 'new-secure-password'
        );
        $this->auth->changePassword(data: $cpData);

        // 4. Login with NEW password
        $newCredentials = new Credentials(identifier: 'itest', password: 'new-secure-password');
        $reLoggedIn     = $this->auth->login(credentials: $newCredentials);
        $this->assertTrue($reLoggedIn->isAuthenticated());

        // 5. Refresh
        $refreshed = $this->auth->refresh(new RefreshAuthenticationRequest($reLoggedIn->refreshToken()));
        $this->assertTrue($refreshed->isAuthenticated());
        $this->assertNotSame($reLoggedIn->refreshToken(), $refreshed->refreshToken());

        // 6. Enroll MFA and verify challenge
        $enrollment  = $this->auth->startMfaEnrollment();
        $backupCodes = $this->auth->confirmMfaEnrollment(new ConfirmMfaEnrollmentData(
                                                             code: (new Totp())->codeAt($enrollment->secret(), new \DateTimeImmutable())
                                                         ));
        $this->assertCount(10, $backupCodes->codes);
        $mfaLogin = $this->auth->login(new Credentials(identifier: 'itest', password: 'new-secure-password'));
        $this->assertTrue($mfaLogin->requiresMfa());
        $this->assertNull($mfaLogin->accessToken());
        $this->assertNull($mfaLogin->refreshToken());
        $mfaCompleted = $this->auth->verifyMfaChallenge(new VerifyMfaChallengeData(
                                                            challengeId: $mfaLogin->mfaChallengeId(),
                                                            code       : $backupCodes->values()[0]
                                                        ));
        $this->assertTrue($mfaCompleted->isAuthenticated());
        $this->assertNotNull($mfaCompleted->context()->mfaVerifiedAt());

        // 7. Backup codes can be regenerated after fresh MFA
        $regeneratedBackupCodes = $this->auth->regenerateBackupCodes();
        $this->assertCount(10, $regeneratedBackupCodes->codes);

        // 8. Email verification
        $verificationChallenge = $this->auth->beginEmailVerification(new BeginEmailVerificationData('integration@test.com'));
        $this->assertTrue($this->auth->verifyEmail(new \Avax\Auth\System\Flow\Verify\VerifyEmailData($verificationChallenge->token ?? '')));

        // 9. Password reset
        $resetChallenge = $this->auth->beginPasswordReset(new BeginPasswordResetData('integration@test.com'));
        $this->assertTrue($this->auth->resetPassword(new ResetPasswordData($resetChallenge->token ?? '', 'reset-password')));

        // 10. Lost-device recovery resets MFA
        $recoveryChallenge = $this->auth->beginMfaRecovery(new BeginMfaRecoveryData('integration@test.com'));
        $this->auth->confirmMfaRecovery(new ConfirmMfaRecoveryData($recoveryChallenge->token ?? ''));

        // 11. Logout
        $this->auth->logout();
        $this->assertTrue(condition: true);
    }

    /**
     * @throws Exception
     */
    public function testUserLifecycleNegative() : void
    {
        // 1. Register user
        $this->auth->register(data: new RegistrationData(email: 'fail@test.com', username: 'fail', password: 'pass'));

        // 2. Try to register with SAME email (Negative)
        $this->expectException(exception: \Avax\Auth\System\Flow\Register\RegistrationFailed::class);
        $this->expectExceptionMessage(message: 'Email is already taken.');
        $this->auth->register(data: new RegistrationData(email: 'fail@test.com', username: 'other', password: 'pass'));
    }

    /**
     * @throws Exception
     */
    public function testLoginNegative() : void
    {
        // 1. Register user
        $this->auth->register(data: new RegistrationData(email: 'login@fail.com', username: 'loginfail', password: 'correct'));

        // 2. Try login with WRONG password (Negative)
        $this->expectException(exception: \Avax\Auth\System\Flow\Login\AuthenticationFailed::class);
        $this->expectExceptionMessage(message: 'Invalid credentials.');
        $this->auth->login(credentials: new Credentials(identifier: 'login@fail.com', password: 'WRONG'));
    }

    protected function setUp() : void
    {
        $this->userSource = new InMemoryUserSource();
        $refreshTokens    = new InMemoryRefreshTokenStore();
        $jwtIdentity      = new JwtIdentity(
            userSource       : $this->userSource,
            codec            : new HmacTokenCodec('integration-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );
        $identity         = new Identity(jwtIdentity: $jwtIdentity);

        $this->auth = Auth::configuration()
            ->forUser(userSource: $this->userSource)
            ->withIdentity(identity: $identity)
            ->withRefreshTokenStore($refreshTokens)
            ->ready();
    }
}
