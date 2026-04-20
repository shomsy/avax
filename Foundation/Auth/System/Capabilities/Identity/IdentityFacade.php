<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Passkey\PasskeyCredential;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\Mfa\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Flows\Mfa\BackupCodeSet;
use Avax\Auth\System\Flows\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flows\Mfa\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Flows\Mfa\Disable\DisableMfa;
use Avax\Auth\System\Flows\Mfa\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Flows\Mfa\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Flows\Mfa\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Flows\Mfa\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Flows\Mfa\MfaChallenge;
use Avax\Auth\System\Flows\Mfa\MfaEnrollment;
use Avax\Auth\System\Flows\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flows\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Flows\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flows\Mfa\Recover\StartMfaRecovery;
use Avax\Auth\System\Flows\Mfa\VerifyMfaChallengeData;
use Avax\Auth\System\Flows\Passkey\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Auth\System\Flows\Passkey\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Flows\Passkey\BeginRegistration\BeginPasskeyRegistration;
use Avax\Auth\System\Flows\Passkey\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Auth\System\Flows\Passkey\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Flows\Passkey\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Auth\System\Flows\Passkey\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Flows\Passkey\ListPasskeys\ListPasskeys;
use Avax\Auth\System\Flows\Passkey\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Flows\Passkey\PasskeyRegistration;
use Avax\Auth\System\Flows\Passkey\RenamePasskey\RenamePasskey;
use Avax\Auth\System\Flows\Passkey\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Flows\Passkey\RevokePasskey\RevokePasskey;
use Avax\Auth\System\Flows\Recover\BeginPasswordReset;
use Avax\Auth\System\Flows\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flows\Recover\PasswordResetChallenge;
use Avax\Auth\System\Flows\Recover\ResetPassword;
use Avax\Auth\System\Flows\Recover\ResetPasswordData;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use Avax\Auth\System\Flows\Session\ActiveSession;
use Avax\Auth\System\Flows\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flows\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Flows\Session\RevokeSession\RevokeSession;
use Avax\Auth\System\Flows\Token\RefreshAuthentication;
use Avax\Auth\System\Flows\Token\RefreshAuthenticationFailed;
use Avax\Auth\System\Flows\Token\RefreshAuthenticationRequest;
use Avax\Auth\System\Flows\Verify\BeginEmailVerification;
use Avax\Auth\System\Flows\Verify\BeginEmailVerificationData;
use Avax\Auth\System\Flows\Verify\EmailVerificationChallenge;
use Avax\Auth\System\Flows\Verify\VerifyEmail;
use Avax\Auth\System\Flows\Verify\VerifyEmailData;
use Random\RandomException;
use RuntimeException;
use SensitiveParameter;

final readonly class IdentityFacade
{
    public function __construct(
        private Login                                                     $login,
        private Logout                                                    $logout,
        #[\SensitiveParameter] private LogoutAllSessions                  $logoutAllSessions,
        #[\SensitiveParameter] private ReadActiveSessions                 $readActiveSessions,
        #[\SensitiveParameter] private RevokeSession                      $revokeSession,
        #[\SensitiveParameter] private ChangePassword                     $changePassword,
        #[\SensitiveParameter] private BeginEmailChange|null              $beginEmailChange,
        #[\SensitiveParameter] private ConfirmEmailChange|null            $confirmEmailChange,
        private Register                                                  $register,
        #[\SensitiveParameter] private RefreshAuthentication              $refreshAuthentication,
        #[\SensitiveParameter] private BeginPasswordReset                 $beginPasswordReset,
        #[\SensitiveParameter] private ResetPassword                      $resetPassword,
        #[\SensitiveParameter] private BeginEmailVerification             $beginEmailVerification,
        #[\SensitiveParameter] private VerifyEmail                        $verifyEmail,
        private StartMfaEnrollment                                        $startMfaEnrollment,
        private ConfirmMfaEnrollment                                      $confirmMfaEnrollment,
        private CancelMfaEnrollment                                       $cancelMfaEnrollment,
        private StartMfaChallenge                                         $startMfaChallenge,
        private VerifyMfaChallenge                                        $verifyMfaChallenge,
        #[\SensitiveParameter] private RegenerateBackupCodes              $regenerateBackupCodes,
        private DisableMfa                                                $disableMfa,
        private StartMfaRecovery                                          $startMfaRecovery,
        private ConfirmMfaRecovery                                        $confirmMfaRecovery,
        private BeginPasskeyRegistration|null                             $beginPasskeyRegistration,
        private CompletePasskeyRegistration|null                          $completePasskeyRegistration,
        #[\SensitiveParameter] private BeginPasskeyAuthentication|null    $beginPasskeyAuthentication,
        #[\SensitiveParameter] private CompletePasskeyAuthentication|null $completePasskeyAuthentication,
        private ListPasskeys|null                                         $readPasskeys,
        private RenamePasskey|null                                        $renamePasskey,
        private RevokePasskey|null                                        $revokePasskey
    ) {}

    /**
     * @param Credentials $credentials
     *
     * @return AuthenticationResult
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions() : void
    {
        $this->logoutAllSessions->execute();
    }

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->readActiveSessions->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->revokeSession->execute(sessionId: $sessionId);
    }

    /**
     * @param ChangePasswordData $data
     *
     * @throws PasswordChangeFailed
     * @throws RateLimitException
     * @throws Unauthenticated
     */
    public function changePassword(ChangePasswordData $data) : void
    {
        $this->changePassword->execute(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->beginEmailChangeOrFail()->execute(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->confirmEmailChangeOrFail()->execute(data: $data);
    }

    /**
     * @param RegistrationData $data
     *
     * @return RegistrationResult
     * @throws RegistrationFailed
     * @throws RateLimitException
     */
    public function register(RegistrationData $data) : RegistrationResult
    {
        return $this->register->execute(data: $data);
    }

    /**
     * @param RefreshAuthenticationRequest $request
     *
     * @return AuthenticationResult
     * @throws RefreshAuthenticationFailed
     * @throws \DateMalformedStringException
     */
    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->refreshAuthentication->execute(request: $request);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->beginPasswordReset->execute(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->resetPassword->execute(data: $data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->beginEmailVerification->execute(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verifyEmail->execute(data: $data);
    }

    /**
     * @return MfaEnrollment
     * @throws Unauthenticated
     * @throws \DateMalformedStringException
     */
    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    /**
     * @param ConfirmMfaEnrollmentData $data
     *
     * @return BackupCodeSet
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment() : void
    {
        $this->cancelMfaEnrollment->execute();
    }

    /**
     * @return MfaChallenge
     * @throws RandomException
     * @throws Unauthenticated
     * @throws \DateMalformedStringException
     */
    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute(data: $data);
    }

    /**
     * @return BackupCodeSet
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa() : void
    {
        $this->disableMfa->execute();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->confirmMfaRecovery->execute(data: $data);
    }

    /**
     * @return PasskeyRegistration
     * @throws RandomException
     * @throws Unauthenticated
     * @throws \DateMalformedStringException
     */
    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->beginPasskeyRegistrationOrFail()->execute();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->completePasskeyRegistrationOrFail()->execute(data: $data);
    }

    /**
     * @param BeginPasskeyAuthenticationData $data
     *
     * @return PasskeyAuthenticationChallenge
     * @throws RandomException
     * @throws \DateMalformedStringException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->beginPasskeyAuthenticationOrFail()->execute(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->completePasskeyAuthenticationOrFail()->execute(data: $data);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->readPasskeysOrFail()->execute();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->renamePasskeyOrFail()->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->revokePasskeyOrFail()->execute(credentialId: $credentialId);
    }

    private function beginEmailChangeOrFail() : BeginEmailChange
    {
        return $this->beginEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
    }

    private function confirmEmailChangeOrFail() : ConfirmEmailChange
    {
        return $this->confirmEmailChange ?? throw new RuntimeException(message: 'Email change flow is not configured.');
    }

    private function beginPasskeyRegistrationOrFail() : BeginPasskeyRegistration
    {
        return $this->beginPasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function completePasskeyRegistrationOrFail() : CompletePasskeyRegistration
    {
        return $this->completePasskeyRegistration ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function beginPasskeyAuthenticationOrFail() : BeginPasskeyAuthentication
    {
        return $this->beginPasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function completePasskeyAuthenticationOrFail() : CompletePasskeyAuthentication
    {
        return $this->completePasskeyAuthentication ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function readPasskeysOrFail() : ListPasskeys
    {
        return $this->readPasskeys ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function renamePasskeyOrFail() : RenamePasskey
    {
        return $this->renamePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }

    private function revokePasskeyOrFail() : RevokePasskey
    {
        return $this->revokePasskey ?? throw new RuntimeException(message: 'Passkey runtime is not configured.');
    }
}
