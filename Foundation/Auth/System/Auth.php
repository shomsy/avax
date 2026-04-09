<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
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
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flow\Mfa\Disable\DisableMfa;
use Avax\Auth\System\Flow\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flow\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryChallenge;
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

/**
 * Main entry point for Avax Auth System.
 *
 * Banal: The facade that connects all system flows.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Login                  $login,
        private AuthenticateRequest    $authenticateRequest,
        private Logout                 $logout,
        private CheckAuthentication    $checkAuthentication,
        private ReadCurrentUser        $readCurrentUser,
        private CurrentAuthentication  $currentAuthentication,
        private AccessInterface        $access,
        private ChangePassword         $changePassword,
        private Register               $register,
        private RefreshAuthentication  $refreshAuthentication,
        private BeginPasswordReset     $beginPasswordReset,
        private ResetPassword          $resetPassword,
        private BeginEmailVerification $beginEmailVerification,
        private VerifyEmail            $verifyEmail,
        private StartMfaEnrollment     $startMfaEnrollment,
        private ConfirmMfaEnrollment   $confirmMfaEnrollment,
        private CancelMfaEnrollment    $cancelMfaEnrollment,
        private StartMfaChallenge      $startMfaChallenge,
        private VerifyMfaChallenge     $verifyMfaChallenge,
        private RegenerateBackupCodes  $regenerateBackupCodes,
        private DisableMfa             $disableMfa,
        private StartMfaRecovery       $startMfaRecovery,
        private ConfirmMfaRecovery     $confirmMfaRecovery
    ) {}

    /**
     * Start the fluent configuration builder.
     */
    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    public function login(Credentials $credentials) : AuthenticationResult
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->authenticateRequest->execute($request);
    }

    public function current() : AuthenticationContext
    {
        return $this->currentAuthentication->read();
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    public function check() : bool
    {
        return $this->checkAuthentication->execute();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->readCurrentUser->execute();
    }

    public function access() : AccessInterface
    {
        return $this->access;
    }

    public function changePassword(ChangePasswordData $data) : void
    {
        $this->changePassword->execute(data: $data);
    }

    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->register->execute(data: $data);
    }

    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->refreshAuthentication->execute($request);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute($data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute($data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute($data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verifyEmail->execute($data);
    }

    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute($data);
    }

    public function cancelMfaEnrollment() : void
    {
        $this->cancelMfaEnrollment->execute();
    }

    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute($data);
    }

    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    public function disableMfa() : void
    {
        $this->disableMfa->execute();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute($data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->confirmMfaRecovery->execute($data);
    }
}
