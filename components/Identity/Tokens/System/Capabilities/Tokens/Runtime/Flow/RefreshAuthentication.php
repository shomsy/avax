<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Flow;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\RefreshTokenRecord;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Rotates refresh tokens and re-issues access state.
 */
final readonly class RefreshAuthentication
{
    public function __construct(
        private UserSourceInterface         $userSource,
        private ProjectAuthenticatedUser    $projectAuthenticatedUser,
        #[SensitiveParameter]
        private CurrentAuthentication       $currentAuthentication,
        private AuditLogInterface           $auditLog,
        private Clock                       $clock,
        #[SensitiveParameter]
        private ?RefreshTokenStoreInterface $refreshTokenStore = null,
        #[SensitiveParameter]
        private ?JwtIdentityInterface       $jwtIdentity = null,
        private ?DeterministicRiskEngine    $deterministicRiskEngine = null,
    ) {}

    /**
     * @throws RefreshAuthenticationFailed
     * @throws DateMalformedStringException
     */
    public function execute(RefreshAuthenticationRequest $refreshAuthenticationRequest) : AuthenticationResult
    {
        if (! $this->refreshTokenStore instanceof RefreshTokenStoreInterface || ! $this->jwtIdentity instanceof JwtIdentityInterface) {
            throw RefreshAuthenticationFailed::invalidToken();
        }

        $record = $this->refreshTokenStore->find(plainToken: $refreshAuthenticationRequest->refreshToken);
        $now    = $this->clock->now();

        if (! $record instanceof RefreshTokenRecord || $record->isExpired()) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.failed',
                                               occurredAt: $now,
                                               context   : [
                                                               'reason'     => 'missing_or_expired',
                                                               'ip_address' => $refreshAuthenticationRequest->ipAddress,
                                                               'user_agent' => $refreshAuthenticationRequest->userAgent,
                                                           ],
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->clientId !== null) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.failed',
                                               occurredAt: $now,
                                               context   : [
                                                               'reason'     => 'oauth_bound_token',
                                                               'ip_address' => $refreshAuthenticationRequest->ipAddress,
                                                               'user_agent' => $refreshAuthenticationRequest->userAgent,
                                                           ],
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        if ($record->isRotated()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $riskDecision = $this->deterministicRiskEngine?->recordRefreshReuse(userId: $record->userId->value, clientId: $record->clientId);
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.refresh.reuse_detected',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'     => $record->userId->value,
                                                               'family_id'   => $record->familyId,
                                                               'risk_action' => $riskDecision?->action->value,
                                                               'ip_address'  => $refreshAuthenticationRequest->ipAddress,
                                                               'user_agent'  => $refreshAuthenticationRequest->userAgent,
                                                           ],
                                           ));

            throw RefreshAuthenticationFailed::invalidToken();
        }

        $user = $this->userSource->findById(id: $record->userId);

        if (! $user instanceof User || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);

            throw RefreshAuthenticationFailed::invalidToken();
        }

        $issuedToken        = $this->jwtIdentity->issue(
            user                : $user,
            phishingResistant   : $record->phishingResistant,
            scopes              : $record->scopes,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            clientId            : $record->clientId,
            refreshTokenFamilyId: $record->familyId,
        );
        $issuedRefreshToken = $this->refreshTokenStore->issue(
            userId           : $record->userId,
            expiresAt        : $now->modify(modifier: '+30 days'),
            familyId         : $record->familyId,
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $record->clientId,
            scopes           : $record->scopes,
        );
        $this->refreshTokenStore->markRotated(tokenId: $record->tokenId, replacementTokenId: $issuedRefreshToken->tokenId);

        $authenticationContext = AuthenticationContext::authenticated(
            accessTokenId       : $issuedToken->tokenId,
            accessTokenExpiresAt: $issuedToken->expiresAt,
            refreshTokenId      : $issuedRefreshToken->tokenId,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant,
            user                : $this->projectAuthenticatedUser->fromUser(user: $user),
            mode                : AuthenticationMode::TOKEN,
        );

        $this->currentAuthentication->store(context: $authenticationContext);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.refresh.succeeded',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $user->getId()->value,
                                                           'family_id'  => $issuedRefreshToken->familyId,
                                                           'ip_address' => $refreshAuthenticationRequest->ipAddress,
                                                           'user_agent' => $refreshAuthenticationRequest->userAgent,
                                                       ],
                                       ));

        return AuthenticationResult::success(
            accessToken : $issuedToken->token,
            refreshToken: $issuedRefreshToken->token,
            context     : $authenticationContext,
        );
    }
}
