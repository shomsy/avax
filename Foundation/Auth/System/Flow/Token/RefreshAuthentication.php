<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Rotates refresh tokens and re-issues access state.
 */
final readonly class RefreshAuthentication
{
    public function __construct(
        private UserSourceInterface             $userSource,
        private ProjectAuthenticatedUser        $projectAuthenticatedUser,
        private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private JwtIdentityInterface|null       $jwtIdentity = null,
        private DeterministicRiskEngine|null    $riskEngine = null
    ) {}

    /**
     * @throws RefreshAuthenticationFailed
     */
    public function execute(RefreshAuthenticationRequest $request) : \Avax\Auth\System\Flow\Login\AuthenticationResult
    {
        if ($this->refreshTokenStore === null || $this->jwtIdentity === null) {
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $record = $this->refreshTokenStore->find($request->refreshToken);
        $now    = $this->clock->now();

        if ($record === null || $record->revoked || $record->isExpiredAt($now)) {
            $this->auditLog->record(new AuditEvent(
                                        name      : 'auth.refresh.failed',
                                        occurredAt: $now,
                                        context   : [
                                                        'reason'     => 'missing_or_expired',
                                                        'ip_address' => $request->ipAddress,
                                                        'user_agent' => $request->userAgent,
                                                    ]
                                    ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->clientId !== null) {
            $this->auditLog->record(new AuditEvent(
                                        name      : 'auth.refresh.failed',
                                        occurredAt: $now,
                                        context   : [
                                                        'reason'     => 'oauth_bound_token',
                                                        'ip_address' => $request->ipAddress,
                                                        'user_agent' => $request->userAgent,
                                                    ]
                                    ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->wasRotated()) {
            $this->refreshTokenStore->revokeFamily($record->familyId);
            $riskDecision = $this->riskEngine?->recordRefreshReuse($record->userId->value, $record->clientId);
            $this->auditLog->record(new AuditEvent(
                                        name      : 'auth.refresh.reuse_detected',
                                        occurredAt: $now,
                                        context   : [
                                                        'user_id'    => $record->userId->value,
                                                        'family_id'  => $record->familyId,
                                                        'risk_action'=> $riskDecision?->action->value,
                                                        'ip_address' => $request->ipAddress,
                                                        'user_agent' => $request->userAgent,
                                                    ]
                                    ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        $user = $this->userSource->findById($record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily($record->familyId);
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $accessToken  = $this->jwtIdentity->issue(
            user         : $user,
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $record->clientId,
            scopes       : $record->scopes
        );
        $refreshToken = $this->refreshTokenStore->issue(
            userId       : $record->userId,
            expiresAt    : $now->modify('+30 days'),
            familyId     : $record->familyId,
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $record->clientId,
            scopes       : $record->scopes
        );
        $this->refreshTokenStore->markRotated($record->tokenId, $refreshToken->tokenId);

        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser($user),
            mode                : AuthenticationMode::TOKEN,
            accessTokenId       : $accessToken->tokenId,
            accessTokenExpiresAt: $accessToken->expiresAt,
            refreshTokenId      : $refreshToken->tokenId,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant
        );

        $this->currentAuthentication->store($context);
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.refresh.succeeded',
                                    occurredAt: $now,
                                    context   : [
                                                    'user_id'    => $user->getId()->value,
                                                    'family_id'  => $refreshToken->familyId,
                                                    'ip_address' => $request->ipAddress,
                                                    'user_agent' => $request->userAgent,
                                                ]
                                ));

        return \Avax\Auth\System\Flow\Login\AuthenticationResult::success(
            context     : $context,
            accessToken : $accessToken->token,
            refreshToken: $refreshToken->token
        );
    }
}
