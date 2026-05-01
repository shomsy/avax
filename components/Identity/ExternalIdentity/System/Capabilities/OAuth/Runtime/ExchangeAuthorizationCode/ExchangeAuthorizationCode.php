<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeRecord;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\AuthorizationCodeStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\PkceMethod;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class ExchangeAuthorizationCode
{
    public function __construct(
        private OAuthClientRegistryInterface    $oAuthClientRegistry,
        #[SensitiveParameter]
        private AuthorizationCodeStoreInterface $authorizationCodeStore,
        private UserSourceInterface $userSource,
        #[SensitiveParameter]
        private JwtIdentityInterface $jwtIdentity,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface $refreshTokenStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter]
        private ?CurrentAuthentication $currentAuthentication = null,
        private ?OidcProviderInterface $oidcProvider = null,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     * @throws DateMalformedStringException
     */
    public function execute(ExchangeAuthorizationCodeData $exchangeAuthorizationCodeData) : OAuthTokenGrant
    {
        $now = $this->clock->now();
        $client = $this->oAuthClientRegistry->find(clientId: $exchangeAuthorizationCodeData->clientId);

        if (! $client instanceof OAuthClient || ! $this->oAuthClientRegistry->verifySecret(clientId: $exchangeAuthorizationCodeData->clientId, plainTextSecret: $exchangeAuthorizationCodeData->clientSecret)) {
            $this->recordFailure(reason: 'client_authentication_failed', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::AUTHORIZATION_CODE)) {
            $this->recordFailure(reason: 'grant_type_not_allowed', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $record = $this->authorizationCodeStore->find(plainCode: $exchangeAuthorizationCodeData->code);

        if (! $record instanceof AuthorizationCodeRecord) {
            $this->recordFailure(reason: 'grant_not_found', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->wasUsed()) {
            $this->recordFailure(reason: 'grant_reused', suspicious: true, data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->isExpiredAt(moment: $now) || $record->clientId !== $exchangeAuthorizationCodeData->clientId) {
            $this->recordFailure(reason: 'grant_expired_or_mismatch', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->redirectUri !== $exchangeAuthorizationCodeData->redirectUri) {
            $this->recordFailure(reason: 'redirect_uri_mismatch', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidRedirectUri();
        }

        if ($client->requiredSenderConstraint instanceof OAuthSenderConstraintType && (! $exchangeAuthorizationCodeData->senderConstraint instanceof OAuthSenderConstraint || $exchangeAuthorizationCodeData->senderConstraint->type !== $client->requiredSenderConstraint)) {
            $this->recordFailure(reason: 'sender_constraint_missing_or_wrong_type', data: $exchangeAuthorizationCodeData);
            throw OAuthTokenExchangeFailed::invalidSenderConstraint();
        }

        if ($record->codeChallenge !== null) {
            if ($record->codeChallengeMethod !== PkceMethod::S256 || $exchangeAuthorizationCodeData->codeVerifier === null) {
                $this->recordFailure(reason: 'pkce_verifier_missing', data: $exchangeAuthorizationCodeData);

                throw OAuthTokenExchangeFailed::invalidVerifier();
            }

            $expectedChallenge = rtrim(
                string    : strtr(base64_encode(string: hash(algo: 'sha256', data: $exchangeAuthorizationCodeData->codeVerifier, binary: true)), '+/', '-_'),
                characters: '=',
            );

            if (! hash_equals(known_string: $record->codeChallenge, user_string: $expectedChallenge)) {
                $this->recordFailure(reason: 'pkce_verifier_mismatch', data: $exchangeAuthorizationCodeData);

                throw OAuthTokenExchangeFailed::invalidVerifier();
            }
        }

        $user = $this->userSource->findById(id: $record->userId);

        if (! $user instanceof User || ! $user->isActive()) {
            $this->recordFailure(reason: 'user_not_active', data: $exchangeAuthorizationCodeData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $this->authorizationCodeStore->markUsed(codeId: $record->codeId, usedAt: $now);

        $issuedRefreshToken = $this->refreshTokenStore->issue(
            userId           : $user->getId(),
            expiresAt        : $now->modify(modifier: '+30 days'),
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $client->clientId,
            scopes           : $record->scopes,
            senderConstraint : $exchangeAuthorizationCodeData->senderConstraint,
        );
        $issuedToken        = $this->jwtIdentity->issue(
            user                : $user,
            phishingResistant   : $record->phishingResistant,
            scopes              : $record->scopes,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            clientId            : $client->clientId,
            senderConstraint    : $exchangeAuthorizationCodeData->senderConstraint,
            refreshTokenFamilyId: $issuedRefreshToken->familyId,
        );
        $idToken = null;

        if (in_array(needle: 'openid', haystack: $record->scopes, strict: true)) {
            if (! $this->oidcProvider instanceof OidcProviderInterface) {
                $this->recordFailure(reason: 'oidc_provider_not_configured', data: $exchangeAuthorizationCodeData);

                throw OAuthTokenExchangeFailed::invalidGrant();
            }

            $idToken = $this->oidcProvider->issueIdToken(
                user             : $user,
                clientId         : $client->clientId,
                scopes           : $record->scopes,
                nonce            : $record->nonce,
                authenticatedAt  : $record->mfaVerifiedAt,
                sessionId        : $this->currentAuthentication?->read()->sessionId(),
                phishingResistant: $record->phishingResistant,
            )->token;
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.authorization_code.exchanged',
            occurredAt: $now,
            context   : [
                            'client_id' => $client->clientId,
                            'user_id'   => $user->getId()->value,
                            'code_id'   => $record->codeId,
                            'scope'     => implode(separator: ' ', array: $record->scopes),
                            'ip_address' => $exchangeAuthorizationCodeData->ipAddress,
                            'user_agent' => $exchangeAuthorizationCodeData->userAgent,
            ],
        ));

        return new OAuthTokenGrant(
            accessToken         : $issuedToken->token,
            accessTokenExpiresAt: $issuedToken->expiresAt,
            refreshToken        : $issuedRefreshToken->token,
            idToken             : $idToken,
            clientId            : $client->clientId,
            userId              : $user->getId()->value,
            scopes              : $record->scopes,
            tokenType           : $exchangeAuthorizationCodeData->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint    : $exchangeAuthorizationCodeData->senderConstraint,
        );
    }

    private function recordFailure(
        ExchangeAuthorizationCodeData $exchangeAuthorizationCodeData,
        string $reason,
        bool $suspicious = false,
    ): void {
        $name = $suspicious
            ? 'auth.oauth.authorization_code.reuse_detected'
            : 'auth.oauth.authorization_code.exchange.failed';

        $this->auditLog->record(event: new AuditEvent(
            name      : $name,
            occurredAt: $this->clock->now(),
            context   : [
                            'client_id'    => $exchangeAuthorizationCodeData->clientId,
                            'redirect_uri' => $exchangeAuthorizationCodeData->redirectUri,
                            'reason'     => $reason,
                            'ip_address'   => $exchangeAuthorizationCodeData->ipAddress,
                            'user_agent'   => $exchangeAuthorizationCodeData->userAgent,
            ],
        ));
    }
}
