<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\BackupCodeSet;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\StartMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Disable\DisableMfa;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaRecoveryChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthentication;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginRegistration\BeginPasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthentication;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\ListPasskeys\ListPasskeys;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskey;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RevokePasskey\RevokePasskey;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthentication;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationFailed;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationRequest;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerification;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmail;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
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
