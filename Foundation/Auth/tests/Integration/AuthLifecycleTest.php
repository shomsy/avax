<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentity;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flows\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\Totp;
use Avax\Auth\System\Flows\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flows\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flows\Recover\ResetPasswordData;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Token\HmacTokenCodec;
use Avax\Auth\System\Flows\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flows\Token\InMemoryTokenRevocationStore;
use Avax\Auth\System\Flows\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flows\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flows\Verify\VerifyEmailData;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Integration test covering the full user lifecycle.
 *
 * Banal: Testing the whole system together.
 */
class AuthLifecycleTest extends TestCase
{
    private Auth               $auth;

    /**
     * @throws Exception
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
        $this->assertTrue(condition: $loggedIn->isAuthenticated());
        $this->assertSame(expected: 'integration@test.com', actual: $loggedIn->user()?->email);
        $this->assertNotNull(actual: $loggedIn->accessToken());
        $this->assertNotNull(actual: $loggedIn->refreshToken());

        $resolved = $this->auth->authenticateRequest(request: AuthenticationRequest::bearer(bearerToken: $loggedIn->accessToken()));
        $this->assertTrue(condition: $resolved->isAuthenticated());

        // 3. Change Password
        $cpData = new ChangePasswordData(
            currentPassword: 'initial-password',
            newPassword    : 'new-secure-password'
        );
        $this->auth->changePassword(data: $cpData);

        // 4. Login with NEW password
        $newCredentials = new Credentials(identifier: 'itest', password: 'new-secure-password');
        $reLoggedIn     = $this->auth->login(credentials: $newCredentials);
        $this->assertTrue(condition: $reLoggedIn->isAuthenticated());

        // 5. Refresh
        $refreshed = $this->auth->refresh(request: new RefreshAuthenticationRequest(refreshToken: $reLoggedIn->refreshToken()));
        $this->assertTrue(condition: $refreshed->isAuthenticated());
        $this->assertNotSame(expected: $reLoggedIn->refreshToken(), actual: $refreshed->refreshToken());

        // 6. Enroll MFA and verify challenge
        $enrollment  = $this->auth->startMfaEnrollment();
        $backupCodes = $this->auth->confirmMfaEnrollment(data: new ConfirmMfaEnrollmentData(
                                                                   code: (new Totp())->codeAt(secret: $enrollment->secret(), moment: new DateTimeImmutable())
                                                               ));
        $this->assertCount(expectedCount: 10, haystack: $backupCodes->codes);
        $mfaLogin = $this->auth->login(credentials: new Credentials(identifier: 'itest', password: 'new-secure-password'));
        $this->assertTrue(condition: $mfaLogin->requiresMfa());
        $this->assertNull(actual: $mfaLogin->accessToken());
        $this->assertNull(actual: $mfaLogin->refreshToken());
        $mfaCompleted = $this->auth->verifyMfaChallenge(data: new VerifyMfaChallengeData(
                                                                  challengeId: $mfaLogin->mfaChallengeId(),
                                                                  code       : $backupCodes->values()[0]
                                                              ));
        $this->assertTrue(condition: $mfaCompleted->isAuthenticated());
        $this->assertNotNull(actual: $mfaCompleted->context()->mfaVerifiedAt());

        // 7. Backup codes can be regenerated after fresh MFA
        $regeneratedBackupCodes = $this->auth->regenerateBackupCodes();
        $this->assertCount(expectedCount: 10, haystack: $regeneratedBackupCodes->codes);

        // 8. Email verification
        $verificationChallenge = $this->auth->beginEmailVerification(data: new BeginEmailVerificationData(email: 'integration@test.com'));
        $this->assertTrue(condition: $this->auth->verifyEmail(data: new VerifyEmailData(token: $verificationChallenge->token ?? '')));

        // 9. Password reset
        $resetChallenge = $this->auth->beginPasswordReset(data: new BeginPasswordResetData(email: 'integration@test.com'));
        $this->assertTrue(condition: $this->auth->resetPassword(data: new ResetPasswordData(token: $resetChallenge->token ?? '', newPassword: 'reset-password')));

        // 10. Lost-device recovery resets MFA
        $recoveryChallenge = $this->auth->beginMfaRecovery(data: new BeginMfaRecoveryData(email: 'integration@test.com'));
        $this->auth->confirmMfaRecovery(data: new ConfirmMfaRecoveryData(token: $recoveryChallenge->token ?? ''));

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
        $this->expectException(exception: RegistrationFailed::class);
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
        $this->expectException(exception: AuthenticationFailed::class);
        $this->expectExceptionMessage(message: 'Invalid credentials.');
        $this->auth->login(credentials: new Credentials(identifier: 'login@fail.com', password: 'WRONG'));
    }

    #[\Override]
    protected function setUp() : void
    {
        $userSource    = new InMemoryUserSource();
        $refreshTokens = new InMemoryRefreshTokenStore();
        $jwtIdentity   = new JwtIdentity(
            userSource       : $userSource,
            codec            : new HmacTokenCodec(secret: 'integration-secret'),
            clock            : new Clock(),
            revocationStore  : new InMemoryTokenRevocationStore(),
            refreshTokenStore: $refreshTokens
        );
        $identity         = new Identity(jwtIdentity: $jwtIdentity);

        $this->auth = Auth::configuration()
            ->forUser(userSource: $userSource)
            ->withIdentity(identity: $identity)
            ->withRefreshTokenStore(refreshTokenStore: $refreshTokens)
            ->ready();
    }
}
