<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\Facades\Account;
use Avax\Auth\System\Capabilities\Identity\Facades\Authentication;
use Avax\Auth\System\Capabilities\Identity\Facades\Recovery;
use Avax\Auth\System\Capabilities\Identity\Facades\Verification;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Mfa;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\BackupCodeSet;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaRecoveryChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\VerifyMfaChallengeData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Passkey;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\BeginAuthentication\BeginPasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication\CompletePasskeyAuthenticationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteRegistration\CompletePasskeyRegistrationData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyAuthenticationChallenge;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\PasskeyRegistration;
use Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\RenamePasskey\RenamePasskeyData;
use Avax\Auth\System\Capabilities\Identity\Passkey\Support\PasskeyCredential;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Auth\System\Capabilities\Identity\Sessions\Sessions;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationFailed;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\RefreshAuthenticationRequest;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeChallenge;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\PasswordResetChallenge;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Avax\Auth\System\Flows\Register\RegistrationResult;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\BeginEmailVerificationData;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\EmailVerificationChallenge;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\VerifyEmailData;
use DateMalformedStringException;
use DateTimeImmutable;
use InvalidArgumentException;
use Random\RandomException;
use SensitiveParameter;

final readonly class Identity implements IdentityInterface
{
    public function __construct(
        private Authentication                $authentication,
        private Sessions                      $session,
        private Account                       $account,
        private Recovery                      $recovery,
        private Verification                  $verification,
        private Mfa                           $mfa,
        private Passkey                       $passkey,
        private SessionIdentityInterface|null $sessionIdentity = null,
        private JwtIdentityInterface|null     $jwtIdentity = null
    ) {}

    public function issue(
        User                   $user,
        DateTimeImmutable|null $mfaVerifiedAt = null,
        bool                   $phishingResistant = false
    ) : IssuedAuthentication
    {
        if (! $user->isActive()) {
            throw new InvalidArgumentException(message: 'Inactive users cannot be authenticated.');
        }

        $refreshToken = $this->jwtIdentity?->issueRefreshToken(
            user             : $user,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
        $sessionId    = $this->sessionIdentity?->issue(
            userId           : $user->getId()->value,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
        $accessToken  = $this->jwtIdentity?->issue(
            user                : $user,
            mfaVerifiedAt       : $mfaVerifiedAt,
            phishingResistant   : $phishingResistant,
            refreshTokenFamilyId: $refreshToken?->familyId
        );

        return new IssuedAuthentication(
            mode             : $this->resolveMode(),
            sessionId        : $sessionId,
            accessToken      : $accessToken,
            refreshToken     : $refreshToken,
            mfaVerifiedAt    : $mfaVerifiedAt,
            phishingResistant: $phishingResistant
        );
    }

    private function resolveMode() : AuthenticationMode
    {
        if ($this->sessionIdentity !== null && $this->jwtIdentity !== null) {
            return AuthenticationMode::HYBRID;
        }

        if ($this->sessionIdentity !== null) {
            return AuthenticationMode::SESSION;
        }

        return AuthenticationMode::TOKEN;
    }

    public function clear(AuthenticationContext|null $context = null) : void
    {
        $this->sessionIdentity?->clear();

        if (
            $context !== null
            && $this->jwtIdentity !== null
            && $context->accessTokenId() !== null
            && $context->accessTokenExpiresAt() !== null
        ) {
            $this->jwtIdentity->revoke(
                tokenId  : $context->accessTokenId(),
                expiresAt: $context->accessTokenExpiresAt()
            );
        }
    }

    public function sessionIdentity() : SessionIdentityInterface|null
    {
        return $this->sessionIdentity;
    }

    public function jwtIdentity() : JwtIdentityInterface|null
    {
        return $this->jwtIdentity;
    }

    /**
     * @param Credentials $credentials
     *
     * @return AuthenticationResult
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->authentication->login(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->authentication->logout();
    }

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions() : void
    {
        $this->session->logoutAllSessions();
    }

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->session->readActiveSessions();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->session->revokeSession(sessionId: $sessionId);
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
        $this->account->changePassword(data: $data);
    }

    public function beginEmailChange(BeginEmailChangeData $data) : EmailChangeChallenge
    {
        return $this->account->beginEmailChange(data: $data);
    }

    public function confirmEmailChange(ConfirmEmailChangeData $data) : bool
    {
        return $this->account->confirmEmailChange(data: $data);
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
        return $this->account->register(data: $data);
    }

    /**
     * @param RefreshAuthenticationRequest $request
     *
     * @return AuthenticationResult
     * @throws RefreshAuthenticationFailed
     * @throws DateMalformedStringException
     */
    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->authentication->refresh(request: $request);
    }

    public function beginPasswordReset(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        return $this->recovery->beginPasswordReset(data: $data);
    }

    public function resetPassword(ResetPasswordData $data) : bool
    {
        return $this->recovery->resetPassword(data: $data);
    }

    public function beginEmailVerification(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        return $this->verification->beginEmailVerification(data: $data);
    }

    public function verifyEmail(VerifyEmailData $data) : bool
    {
        return $this->verification->verifyEmail(data: $data);
    }

    /**
     * @return MfaEnrollment
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->mfa->startMfaEnrollment();
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
        return $this->mfa->confirmMfaEnrollment(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment() : void
    {
        $this->mfa->cancelMfaEnrollment();
    }

    /**
     * @return MfaChallenge
     * @throws RandomException
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->mfa->beginMfaChallenge();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->mfa->verifyMfaChallenge(data: $data);
    }

    /**
     * @return BackupCodeSet
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->mfa->regenerateBackupCodes();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa() : void
    {
        $this->mfa->disableMfa();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->mfa->beginMfaRecovery(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->mfa->confirmMfaRecovery(data: $data);
    }

    /**
     * @return PasskeyRegistration
     * @throws RandomException
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function beginPasskeyRegistration() : PasskeyRegistration
    {
        return $this->passkey->beginPasskeyRegistration();
    }

    public function completePasskeyRegistration(CompletePasskeyRegistrationData $data) : PasskeyCredential
    {
        return $this->passkey->completePasskeyRegistration(data: $data);
    }

    /**
     * @param BeginPasskeyAuthenticationData $data
     *
     * @return PasskeyAuthenticationChallenge
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function beginPasskeyAuthentication(BeginPasskeyAuthenticationData $data) : PasskeyAuthenticationChallenge
    {
        return $this->passkey->beginPasskeyAuthentication(data: $data);
    }

    public function completePasskeyAuthentication(CompletePasskeyAuthenticationData $data) : AuthenticationResult
    {
        return $this->passkey->completePasskeyAuthentication(data: $data);
    }

    /**
     * @return list<PasskeyCredential>
     */
    public function readPasskeys() : array
    {
        return $this->passkey->readPasskeys();
    }

    public function renamePasskey(RenamePasskeyData $data) : PasskeyCredential
    {
        return $this->passkey->renamePasskey(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function revokePasskey(#[SensitiveParameter] string $credentialId) : void
    {
        $this->passkey->revokePasskey(credentialId: $credentialId);
    }
}
