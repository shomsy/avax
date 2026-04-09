<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\Login\AuthenticationResult;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\Mfa\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\BackupCode;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Disable\DisableMfa;
use Avax\Auth\System\Flow\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Mfa\MfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flow\Mfa\MfaStatus;
use Avax\Auth\System\Flow\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\StartMfaRecovery;
use Avax\Auth\System\Flow\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\Recover\BeginPasswordReset;
use Avax\Auth\System\Flow\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flow\Recover\PasswordResetChallenge;
use Avax\Auth\System\Flow\Recover\ResetPassword;
use Avax\Auth\System\Flow\Recover\ResetPasswordData;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\Register\RegistrationResult;
use Avax\Auth\System\Flow\Token\RefreshAuthentication;
use Avax\Auth\System\Flow\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flow\Verify\BeginEmailVerification;
use Avax\Auth\System\Flow\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flow\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flow\Verify\VerifyEmail;
use Avax\Auth\System\Flow\Verify\VerifyEmailData;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Auth Facade.
 */
class AuthTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testAuthFacadeDelegatesToFlows() : void
    {
        $loginFlow              = Mockery::mock(Login::class);
        $authenticateRequest    = Mockery::mock(AuthenticateRequest::class);
        $logoutFlow             = Mockery::mock(Logout::class);
        $checkFlow              = Mockery::mock(CheckAuthentication::class);
        $readUserFlow           = Mockery::mock(ReadCurrentUser::class);
        $currentAuthentication  = new CurrentAuthentication();
        $access                 = Mockery::mock(AccessInterface::class);
        $changePasswordFlow     = Mockery::mock(ChangePassword::class);
        $registerFlow           = Mockery::mock(Register::class);
        $refreshFlow            = Mockery::mock(RefreshAuthentication::class);
        $beginPasswordReset     = Mockery::mock(BeginPasswordReset::class);
        $resetPassword          = Mockery::mock(ResetPassword::class);
        $beginEmailVerification = Mockery::mock(BeginEmailVerification::class);
        $verifyEmail            = Mockery::mock(VerifyEmail::class);
        $startMfaEnrollment     = Mockery::mock(StartMfaEnrollment::class);
        $confirmMfaEnrollment   = Mockery::mock(ConfirmMfaEnrollment::class);
        $cancelMfaEnrollment    = Mockery::mock(CancelMfaEnrollment::class);
        $beginMfaChallenge      = Mockery::mock(StartMfaChallenge::class);
        $verifyMfaChallenge     = Mockery::mock(VerifyMfaChallenge::class);
        $regenerateBackupCodes  = Mockery::mock(RegenerateBackupCodes::class);
        $disableMfa             = Mockery::mock(DisableMfa::class);
        $startMfaRecovery       = Mockery::mock(StartMfaRecovery::class);
        $confirmMfaRecovery     = Mockery::mock(ConfirmMfaRecovery::class);

        $auth = new Auth(
            login                 : $loginFlow,
            authenticateRequest   : $authenticateRequest,
            logout                : $logoutFlow,
            checkAuthentication   : $checkFlow,
            readCurrentUser       : $readUserFlow,
            currentAuthentication : $currentAuthentication,
            access                : $access,
            changePassword        : $changePasswordFlow,
            register              : $registerFlow,
            refreshAuthentication : $refreshFlow,
            beginPasswordReset    : $beginPasswordReset,
            resetPassword         : $resetPassword,
            beginEmailVerification: $beginEmailVerification,
            verifyEmail           : $verifyEmail,
            startMfaEnrollment    : $startMfaEnrollment,
            confirmMfaEnrollment  : $confirmMfaEnrollment,
            cancelMfaEnrollment   : $cancelMfaEnrollment,
            startMfaChallenge     : $beginMfaChallenge,
            verifyMfaChallenge    : $verifyMfaChallenge,
            regenerateBackupCodes : $regenerateBackupCodes,
            disableMfa            : $disableMfa,
            startMfaRecovery      : $startMfaRecovery,
            confirmMfaRecovery    : $confirmMfaRecovery
        );

        $user        = new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user');
        $context     = AuthenticationContext::authenticated($user, AuthenticationMode::TOKEN);
        $credentials = new Credentials(identifier: 'user', password: 'pass');
        $loginResult = AuthenticationResult::success($context, 'token', 'refresh');
        $loginFlow->shouldReceive('execute')->with($credentials)->andReturn($loginResult);
        $this->assertSame(expected: $loginResult, actual: $auth->login(credentials: $credentials));

        $request = AuthenticationRequest::bearer('token');
        $authenticateRequest->shouldReceive('execute')->with($request)->andReturn($context);
        $this->assertSame($context, $auth->authenticateRequest($request));

        $logoutFlow->shouldReceive('execute')->once();
        $auth->logout();

        $checkFlow->shouldReceive('execute')->andReturn(true);
        $this->assertTrue(condition: $auth->check());

        $readUserFlow->shouldReceive('execute')->andReturn($user);
        $this->assertSame(expected: $user, actual: $auth->user());

        $this->assertSame(expected: $access, actual: $auth->access());

        $cpData = new ChangePasswordData(currentPassword: 'old', newPassword: 'new');
        $changePasswordFlow->shouldReceive('execute')->with($cpData)->once();
        $auth->changePassword(data: $cpData);

        $regData            = new RegistrationData(email: 'email', username: 'nick', password: 'pass');
        $registrationResult = new RegistrationResult($user);
        $registerFlow->shouldReceive('execute')->with($regData)->andReturn($registrationResult);
        $this->assertSame(expected: $registrationResult, actual: $auth->register(data: $regData));

        $refreshRequest = new RefreshAuthenticationRequest('refresh');
        $refreshFlow->shouldReceive('execute')->with($refreshRequest)->andReturn($loginResult);
        $this->assertSame($loginResult, $auth->refresh($refreshRequest));

        $passwordResetData      = new BeginPasswordResetData('user@example.com');
        $passwordResetChallenge = new PasswordResetChallenge(true, 'reset');
        $beginPasswordReset->shouldReceive('execute')->with($passwordResetData)->andReturn($passwordResetChallenge);
        $this->assertSame($passwordResetChallenge, $auth->beginPasswordReset($passwordResetData));

        $completeReset = new ResetPasswordData('reset', 'new');
        $resetPassword->shouldReceive('execute')->with($completeReset)->andReturn(true);
        $this->assertTrue($auth->resetPassword($completeReset));

        $beginVerify                = new BeginEmailVerificationData('user@example.com');
        $emailVerificationChallenge = new EmailVerificationChallenge(true, 'verify');
        $beginEmailVerification->shouldReceive('execute')->with($beginVerify)->andReturn($emailVerificationChallenge);
        $this->assertSame($emailVerificationChallenge, $auth->beginEmailVerification($beginVerify));

        $verifyData = new VerifyEmailData('verify');
        $verifyEmail->shouldReceive('execute')->with($verifyData)->andReturn(true);
        $this->assertTrue($auth->verifyEmail($verifyData));

        $enrollment = new MfaEnrollment(
            method      : MfaMethod::TOTP,
            status      : MfaStatus::ENROLLMENT_PENDING,
            accountLabel: 'user@example.com',
            issuer      : 'Acme',
            secret      : 'SECRET',
            otpauthUri  : 'otpauth://totp/test',
            startedAt   : new \DateTimeImmutable('-1 minute'),
            expiresAt   : new \DateTimeImmutable('+5 minutes')
        );
        $startMfaEnrollment->shouldReceive('execute')->once()->andReturn($enrollment);
        $this->assertSame($enrollment, $auth->startMfaEnrollment());

        $backupCodes       = new BackupCodeSet(
            codes      : [new BackupCode('ABCD-EF01')],
            generatedAt: new \DateTimeImmutable()
        );
        $confirmEnrollment = new ConfirmMfaEnrollmentData('123456');
        $confirmMfaEnrollment->shouldReceive('execute')->with($confirmEnrollment)->andReturn($backupCodes);
        $this->assertSame($backupCodes, $auth->confirmMfaEnrollment($confirmEnrollment));

        $cancelMfaEnrollment->shouldReceive('execute')->once();
        $auth->cancelMfaEnrollment();

        $mfaChallenge = new MfaChallenge(
            challengeId      : 'challenge',
            purpose          : MfaChallengePurpose::STEP_UP,
            expiresAt        : new \DateTimeImmutable('+5 minutes'),
            remainingAttempts: 5
        );
        $beginMfaChallenge->shouldReceive('execute')->andReturn($mfaChallenge);
        $this->assertSame($mfaChallenge, $auth->beginMfaChallenge());

        $verifyMfaData = new VerifyMfaChallengeData('challenge', '123456');
        $verifyMfaChallenge->shouldReceive('execute')->with($verifyMfaData)->andReturn($loginResult);
        $this->assertSame($loginResult, $auth->verifyMfaChallenge($verifyMfaData));

        $regenerateBackupCodes->shouldReceive('execute')->andReturn($backupCodes);
        $this->assertSame($backupCodes, $auth->regenerateBackupCodes());

        $disableMfa->shouldReceive('execute')->once();
        $auth->disableMfa();

        $recoveryData      = new BeginMfaRecoveryData('user@example.com');
        $recoveryChallenge = new MfaRecoveryChallenge(true, 'recover', new \DateTimeImmutable('+15 minutes'));
        $startMfaRecovery->shouldReceive('execute')->with($recoveryData)->andReturn($recoveryChallenge);
        $this->assertSame($recoveryChallenge, $auth->beginMfaRecovery($recoveryData));

        $confirmRecoveryData = new ConfirmMfaRecoveryData('recover');
        $confirmMfaRecovery->shouldReceive('execute')->with($confirmRecoveryData)->once();
        $auth->confirmMfaRecovery($confirmRecoveryData);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
