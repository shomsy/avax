<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Login;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use SensitiveParameter;

/**
 * High-level orchestrator for the login process.
 *
 * Banal: The main Login file.
 */
final readonly class Login
{
    public function __construct(
        private UserSourceInterface                     $userSource,
        #[SensitiveParameter] private PasswordHasher    $passwordHasher,
        #[SensitiveParameter] private IdentityInterface $identity,
        private ProjectAuthenticatedUser                $projectAuthenticatedUser,
        private CurrentAuthentication                   $currentAuthentication,
        private AuditLogInterface                       $auditLog,
        private MfaStoreInterface                       $mfaStore,
        private StartMfaChallenge                       $startMfaChallenge,
        private LoginRateLimit|null                     $rateLimit = null,
        private DeterministicRiskEngine|null            $riskEngine = null
    ) {}

    /**
     * @throws AuthenticationFailed
     */
    public function execute(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        $this->rateLimit?->check(identifier: $credentials->identifier);

        $user = $this->userSource->findByCredentials(credentials: $credentials);

        if (
            $user === null
            || ! $this->passwordHasher->verify(password: $credentials->password, hash: $user->getPasswordHash())
            || ! $user->isActive()
        ) {
            $this->rateLimit?->recordFailed(identifier: $credentials->identifier);
            $this->auditLog->record(new AuditEvent(
                                        name      : 'auth.login.failed',
                                        occurredAt: new \DateTimeImmutable(),
                                        context   : [
                                                        'identifier' => strtolower($credentials->identifier),
                                                        'ip_address' => $credentials->ipAddress,
                                                        'user_agent' => $credentials->userAgent,
                                                    ]
                                    ));

            throw AuthenticationFailed::invalidCredentials();
        }

        if ($this->passwordHasher->needsRehash($user->getPasswordHash())) {
            $this->userSource->updatePassword(
                id          : $user->getId(),
                passwordHash: $this->passwordHasher->hash($credentials->password)
            );
        }

        if ($this->mfaStore->isEnabled($user->getId())) {
            $challenge = $this->startMfaChallenge->issueForLogin(
                user     : $user,
                ipAddress: $credentials->ipAddress,
                userAgent: $credentials->userAgent
            );

            return AuthenticationResult::mfaRequired(
                user     : $this->projectAuthenticatedUser->fromUser($user),
                challenge: $challenge
            );
        }

        $issued  = $this->identity->issue(user: $user);
        $this->identity->sessionIdentity()?->captureCurrentSession(
            ipAddress: $credentials->ipAddress,
            userAgent: $credentials->userAgent
        );
        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser($user),
            mode                : $issued->mode,
            sessionId           : $issued->sessionId,
            accessTokenId       : $issued->accessToken?->tokenId,
            accessTokenExpiresAt: $issued->accessToken?->expiresAt,
            refreshTokenId      : $issued->refreshToken?->tokenId,
            mfaVerifiedAt       : $issued->mfaVerifiedAt,
            phishingResistant   : $issued->phishingResistant
        );

        $this->currentAuthentication->store($context);
        $riskDecision = $this->riskEngine?->assessSuccessfulAuthentication(
            user      : $user,
            ipAddress : $credentials->ipAddress,
            userAgent : $credentials->userAgent
        );

        $this->rateLimit?->reset(identifier: $credentials->identifier);
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.login.succeeded',
                                    occurredAt: new \DateTimeImmutable(),
                                    context   : [
                                                    'user_id'    => $user->getId()->value,
                                                    'mode'       => $issued->mode->value,
                                                    'risk_action'=> $riskDecision?->action->value,
                                                    'ip_address' => $credentials->ipAddress,
                                                    'user_agent' => $credentials->userAgent,
                                                ]
                                ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $issued->accessToken?->token,
            refreshToken: $issued->refreshToken?->token
        );
    }
}
