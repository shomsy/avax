<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Rotates refresh tokens and re-issues access state.
 */
final readonly class RefreshAuthentication
{
    public function __construct(
        private UserSourceInterface                                   $userSource,
        private ProjectAuthenticatedUser                              $projectAuthenticatedUser,
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        #[SensitiveParameter] private JwtIdentityInterface|null       $jwtIdentity = null,
        private DeterministicRiskEngine|null                          $riskEngine = null
    ) {}

    /**
     * @throws RefreshAuthenticationFailed
     * @throws DateMalformedStringException
     */
    public function execute(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        if ($this->refreshTokenStore === null || $this->jwtIdentity === null) {
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $record = $this->refreshTokenStore->find(plainToken: $request->refreshToken);
        $now    = $this->clock->now();

        if ($record === null || $record->isExpired()) {
            $this->auditLog->record(event: new AuditEvent(
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
            $this->auditLog->record(event: new AuditEvent(
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

        if ($record->isRotated()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $riskDecision = $this->riskEngine?->recordRefreshReuse(userId: $record->userId->value, clientId: $record->clientId);
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.reuse_detected',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'     => $record->userId->value,
                                                               'family_id'   => $record->familyId,
                                                               'risk_action' => $riskDecision?->action->value,
                                                               'ip_address'  => $request->ipAddress,
                                                               'user_agent'  => $request->userAgent,
                                                           ]
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        $user = $this->userSource->findById(id: $record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $accessToken  = $this->jwtIdentity->issue(
            user                : $user,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant,
            clientId            : $record->clientId,
            scopes              : $record->scopes,
            refreshTokenFamilyId: $record->familyId
        );
        $refreshToken = $this->refreshTokenStore->issue(
            userId           : $record->userId,
            expiresAt        : $now->modify(modifier: '+30 days'),
            familyId         : $record->familyId,
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $record->clientId,
            scopes           : $record->scopes
        );
        $this->refreshTokenStore->markRotated(tokenId: $record->tokenId, replacementTokenId: $refreshToken->tokenId);

        $context = AuthenticationContext::authenticated(
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : AuthenticationMode::TOKEN,
            accessTokenId       : $accessToken->tokenId,
            accessTokenExpiresAt: $accessToken->expiresAt,
            refreshTokenId      : $refreshToken->tokenId,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant
        );

        $this->currentAuthentication->store(context: $context);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.refresh.succeeded',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $user->getId()->value,
                                                           'family_id'  => $refreshToken->familyId,
                                                           'ip_address' => $request->ipAddress,
                                                           'user_agent' => $request->userAgent,
                                                       ]
                                       ));

        return AuthenticationResult::success(
            context     : $context,
            accessToken : $accessToken->token,
            refreshToken: $refreshToken->token
        );
    }
}
