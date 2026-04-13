<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class ExchangeRefreshToken
{
    public function __construct(
        private OAuthClientRegistryInterface                     $clientRegistry,
        #[SensitiveParameter] private RefreshTokenStoreInterface $refreshTokenStore,
        private UserSourceInterface                              $userSource,
        #[SensitiveParameter] private JwtIdentityInterface       $jwtIdentity,
        private AuditLogInterface                                $auditLog,
        private Clock                                            $clock,
        private DeterministicRiskEngine|null                     $riskEngine = null
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     * @throws \DateMalformedStringException
     */
    public function execute(ExchangeRefreshTokenData $data) : OAuthTokenGrant
    {
        $now    = $this->clock->now();
        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret(clientId: $data->clientId, plainTextSecret: $data->clientSecret)) {
            $this->recordFailure(data: $data, reason: 'client_authentication_failed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::REFRESH_TOKEN)) {
            $this->recordFailure(data: $data, reason: 'grant_type_not_allowed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $record = $this->refreshTokenStore->find(plainToken: $data->refreshToken);

        if (
            $record === null
            || $record->clientId !== $data->clientId
            || $record->revoked
            || $record->isExpiredAt(moment: $now)
        ) {
            $this->recordFailure(data: $data, reason: 'grant_not_found_or_expired');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($client->requiredSenderConstraint !== null) {
            if (
                $data->senderConstraint === null
                || $data->senderConstraint->type !== $client->requiredSenderConstraint
                || $record->senderConstraint === null
                || ! $record->senderConstraint->equals(other: $data->senderConstraint)
            ) {
                $this->recordFailure(data: $data, reason: 'sender_constraint_mismatch');
                throw OAuthTokenExchangeFailed::invalidSenderConstraint();
            }
        }

        if ($record->wasRotated()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $riskDecision = $this->riskEngine?->recordRefreshReuse(userId: $record->userId->value, clientId: $record->clientId);
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.oauth.refresh.review.opened',
                occurredAt: $now,
                context   : [
                    'user_id' => $record->userId->value,
                    'client_id' => $record->clientId,
                    'risk_action' => $riskDecision?->action->value,
                ]
            ));
            $this->recordFailure(data: $data, reason: 'reuse_detected', suspicious: true);
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $user = $this->userSource->findById(id: $record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->refreshTokenStore->revokeFamily(familyId: $record->familyId);
            $this->recordFailure(data: $data, reason: 'user_not_active');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $accessToken = $this->jwtIdentity->issue(
            user         : $user,
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $client->clientId,
            scopes       : $record->scopes,
            senderConstraint: $record->senderConstraint
        );
        $refreshToken = $this->refreshTokenStore->issue(
            userId       : $record->userId,
            expiresAt    : $now->modify(modifier: '+30 days'),
            familyId     : $record->familyId,
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $client->clientId,
            scopes       : $record->scopes,
            senderConstraint: $record->senderConstraint
        );

        $this->refreshTokenStore->markRotated(tokenId: $record->tokenId, replacementTokenId: $refreshToken->tokenId);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.refresh.exchanged',
            occurredAt: $now,
            context   : [
                'client_id' => $client->clientId,
                'user_id' => $user->getId()->value,
                'family_id' => $refreshToken->familyId,
                'scope' => implode(' ', $record->scopes),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return new OAuthTokenGrant(
            accessToken          : $accessToken->token,
            accessTokenExpiresAt : $accessToken->expiresAt,
            refreshToken         : $refreshToken->token,
            idToken              : null,
            clientId             : $client->clientId,
            userId               : $user->getId()->value,
            scopes               : $record->scopes,
            tokenType            : $record->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint     : $record->senderConstraint
        );
    }

    private function recordFailure(
        ExchangeRefreshTokenData $data,
        string $reason,
        bool $suspicious = false
    ) : void
    {
        $name = $suspicious
            ? 'auth.oauth.refresh.reuse_detected'
            : 'auth.oauth.refresh.failed';

        $this->auditLog->record(event: new AuditEvent(
            name      : $name,
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'reason' => $reason,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
