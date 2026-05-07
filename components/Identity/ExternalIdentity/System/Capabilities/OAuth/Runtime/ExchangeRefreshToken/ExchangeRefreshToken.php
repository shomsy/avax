<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\RefreshTokenRecord;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class ExchangeRefreshToken
{
    public function __construct(
        private OAuthClientRegistryInterface $oAuthClientRegistry,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface   $refreshTokenStore,
        private UserSourceInterface          $userSource,
        #[SensitiveParameter]
        private JwtIdentityInterface         $jwtIdentity,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock,
        private ?DeterministicRiskEngine     $deterministicRiskEngine = null,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     * @throws DateMalformedStringException
     */
    public function execute(ExchangeRefreshTokenData $exchangeRefreshTokenData) : OAuthTokenGrant
    {
        $now    = $this->clock->now();
        $client = $this->oAuthClientRegistry->find(clientId: $exchangeRefreshTokenData->clientId);

        if (! $client instanceof OAuthClient || ! $this->oAuthClientRegistry->verifySecret(clientId: $exchangeRefreshTokenData->clientId, plainTextSecret: $exchangeRefreshTokenData->clientSecret)) {
            $this->recordFailure(reason: 'client_authentication_failed', data: $exchangeRefreshTokenData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::REFRESH_TOKEN)) {
            $this->recordFailure(reason: 'grant_type_not_allowed', data: $exchangeRefreshTokenData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $record = $this->refreshTokenStore->find(plainToken: $exchangeRefreshTokenData->refreshToken);

        if (
            ! $record instanceof RefreshTokenRecord
            || $record->clientId !== $exchangeRefreshTokenData->clientId
            || $record->revoked
            || $record->isExpiredAt(moment: $now)
        ) {
            $this->recordFailure(reason: 'grant_not_found_or_expired', data: $exchangeRefreshTokenData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($client->requiredSenderConstraint instanceof OAuthSenderConstraintType && (! $exchangeRefreshTokenData->senderConstraint instanceof OAuthSenderConstraint || $exchangeRefreshTokenData->senderConstraint->type !== $client->requiredSenderConstraint || ! $record->senderConstraint instanceof OAuthSenderConstraint || ! $record->senderConstraint->equals(other: $exchangeRefreshTokenData->senderConstraint))) {
            $this->recordFailure(reason: 'sender_constraint_mismatch', data: $exchangeRefreshTokenData);
            throw OAuthTokenExchangeFailed::invalidSenderConstraint();
        }

        if ($record->wasRotated()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $riskDecision = $this->deterministicRiskEngine?->recordRefreshReuse(userId: $record->userId->value, clientId: $record->clientId);
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.oauth.refresh.review.opened',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id'     => $record->userId->value,
                                                               'client_id'   => $record->clientId,
                                                               'risk_action' => $riskDecision?->action->value,
                                                           ],
                                           ));
            $this->recordFailure(reason: 'reuse_detected', suspicious: true, data: $exchangeRefreshTokenData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $user = $this->userSource->findById(id: $record->userId);

        if (! $user instanceof User || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $this->recordFailure(reason: 'user_not_active', data: $exchangeRefreshTokenData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $issuedToken        = $this->jwtIdentity->issue(
            user                : $user,
            phishingResistant   : $record->phishingResistant,
            scopes              : $record->scopes,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            clientId            : $client->clientId,
            senderConstraint    : $record->senderConstraint,
            refreshTokenFamilyId: $record->familyId,
        );
        $issuedRefreshToken = $this->refreshTokenStore->issue(
            userId           : $record->userId,
            expiresAt        : $now->modify(modifier: '+30 days'),
            familyId         : $record->familyId,
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $client->clientId,
            scopes           : $record->scopes,
            senderConstraint : $record->senderConstraint,
        );

        $this->refreshTokenStore->markRotated(tokenId: $record->tokenId, replacementTokenId: $issuedRefreshToken->tokenId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.refresh.exchanged',
                                           occurredAt: $now,
                                           context   : [
                                                           'client_id'  => $client->clientId,
                                                           'user_id'    => $user->getId()->value,
                                                           'family_id'  => $issuedRefreshToken->familyId,
                                                           'scope'      => implode(separator: ' ', array: $record->scopes),
                                                           'ip_address' => $exchangeRefreshTokenData->ipAddress,
                                                           'user_agent' => $exchangeRefreshTokenData->userAgent,
                                                       ],
                                       ));

        return new OAuthTokenGrant(
            accessToken         : $issuedToken->token,
            accessTokenExpiresAt: $issuedToken->expiresAt,
            refreshToken        : $issuedRefreshToken->token,
            idToken             : null,
            clientId            : $client->clientId,
            userId              : $user->getId()->value,
            scopes              : $record->scopes,
            tokenType           : $record->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint    : $record->senderConstraint,
        );
    }

    private function recordFailure(
        ExchangeRefreshTokenData $exchangeRefreshTokenData,
        string                   $reason,
        bool                     $suspicious = false,
    ) : void
    {
        $name = $suspicious
            ? 'auth.oauth.refresh.reuse_detected'
            : 'auth.oauth.refresh.failed';

        $this->auditLog->record(event: new AuditEvent(
                                           name      : $name,
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'  => $exchangeRefreshTokenData->clientId,
                                                           'reason'     => $reason,
                                                           'ip_address' => $exchangeRefreshTokenData->ipAddress,
                                                           'user_agent' => $exchangeRefreshTokenData->userAgent,
                                                       ],
                                       ));
    }
}
