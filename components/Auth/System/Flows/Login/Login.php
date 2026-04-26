<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Login;

use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class Login
{
    public function __construct(
        private UserSourceInterface                         $userSource,
        #[SensitiveParameter] private PasswordHasher        $passwordHasher,
        #[SensitiveParameter] private IdentityInterface     $identity,
        private ProjectAuthenticatedUser                    $projectAuthenticatedUser,
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private AuditLogInterface                           $auditLog,
        private MfaStoreInterface                           $mfaStore,
        private StartMfaChallenge                           $startMfaChallenge,
        private Clock                                       $clock,
        private LoginRateLimit|null                         $rateLimit = null,
        private DeterministicRiskEngine|null                $riskEngine = null,
    ) {}

    /**
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function execute(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        $this->checkRateLimit(credentials: $credentials);

        $user = $this->authenticate(credentials: $credentials);

        $this->rehashPasswordIfNeeded(user: $user, credentials: $credentials);

        if ($this->mfaStore->isEnabled(userId: $user->getId())) {
            return $this->startMfa(user: $user, credentials: $credentials);
        }

        return $this->completeLogin(user: $user, credentials: $credentials);
    }

    /**
     * @throws RateLimitException
     */
    private function checkRateLimit(#[SensitiveParameter] Credentials $credentials) : void
    {
        $this->rateLimit?->check(identifier: $credentials->identifier);
    }

    /**
     * @throws AuthenticationFailed
     */
    private function authenticate(#[SensitiveParameter] Credentials $credentials) : User
    {
        $user = $this->userSource->findByCredentials(credentials: $credentials);
        $hash = $user?->getPasswordHash() ?? $this->passwordHasher->dummyHash();

        $passwordValid = $this->passwordHasher->verify(
            password: $credentials->password,
            hash    : $hash,
        );

        if ($user !== null && $passwordValid && $user->isActive()) {
            return $user;
        }

        $this->failAuthentication(credentials: $credentials);
    }

    /**
     * @throws AuthenticationFailed
     */
    private function failAuthentication(#[SensitiveParameter] Credentials $credentials) : never
    {
        $this->rateLimit?->recordFailed(identifier: $credentials->identifier);

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.login.failed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'identifier' => strtolower(string: $credentials->identifier),
                                                           'ip_address' => $credentials->ipAddress,
                                                           'user_agent' => $credentials->userAgent,
                                                       ],
                                       ));

        throw AuthenticationFailed::invalidCredentials();
    }

    private function rehashPasswordIfNeeded(User $user, #[SensitiveParameter] Credentials $credentials) : void
    {
        if (! $this->passwordHasher->needsRehash(hash: $user->getPasswordHash())) {
            return;
        }

        $this->userSource->updatePassword(
            id          : $user->getId(),
            passwordHash: $this->passwordHasher->hash(password: $credentials->password),
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    private function startMfa(User $user, #[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        $challenge = $this->startMfaChallenge->issueForLogin(
            user     : $user,
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent,
        );

        return AuthenticationResult::mfaRequired(
            user     : $this->projectAuthenticatedUser->fromUser(user: $user),
            challenge: $challenge,
        );
    }

    private function completeLogin(User $user, #[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        $issued = $this->identity->issue(user: $user);

        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent,
        );

        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $issued->mfaVerifiedAt,
            phishingResistant   : $issued->phishingResistant,
        );

        $this->currentAuthentication->store(context: $context);

        $riskDecision = $this->riskEngine?->assessSuccessfulAuthentication(
            user     : $user,
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent,
        );

        $this->rateLimit?->reset(identifier: $credentials->identifier);

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.login.succeeded',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'     => $user->getId()->value,
                                                           'mode'        => $issued->mode->value,
                                                           'risk_action' => $riskDecision?->action->value,
                                                           'ip_address'  => $credentials->ipAddress,
                                                           'user_agent'  => $credentials->userAgent,
                                                       ],
                                       ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token,
        );
    }
}
