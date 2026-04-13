<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\OAuth\AuthorizationCodeStoreInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class ExchangeAuthorizationCode
{
    public function __construct(
        private OAuthClientRegistryInterface    $clientRegistry,
        private AuthorizationCodeStoreInterface $codeStore,
        private UserSourceInterface             $userSource,
        private JwtIdentityInterface            $jwtIdentity,
        private RefreshTokenStoreInterface      $refreshTokenStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private OidcProviderInterface|null      $oidcProvider = null
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        $now    = $this->clock->now();
        $client = $this->clientRegistry->find($data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret($data->clientId, $data->clientSecret)) {
            $this->recordFailure($data, 'client_authentication_failed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(OAuthGrantType::AUTHORIZATION_CODE)) {
            $this->recordFailure($data, 'grant_type_not_allowed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $record = $this->codeStore->find($data->code);

        if ($record === null) {
            $this->recordFailure($data, 'grant_not_found');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->wasUsed()) {
            $this->recordFailure($data, 'grant_reused', suspicious: true);
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->isExpiredAt($now) || $record->clientId !== $data->clientId) {
            $this->recordFailure($data, 'grant_expired_or_mismatch');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->redirectUri !== $data->redirectUri) {
            $this->recordFailure($data, 'redirect_uri_mismatch');
            throw OAuthTokenExchangeFailed::invalidRedirectUri();
        }

        if ($client->requiredSenderConstraint !== null) {
            if (
                $data->senderConstraint === null
                || $data->senderConstraint->type !== $client->requiredSenderConstraint
            ) {
                $this->recordFailure($data, 'sender_constraint_missing_or_wrong_type');
                throw OAuthTokenExchangeFailed::invalidSenderConstraint();
            }
        }

        if ($record->codeChallenge !== null) {
            if ($record->codeChallengeMethod !== PkceMethod::S256 || $data->codeVerifier === null) {
                $this->recordFailure($data, 'pkce_verifier_missing');
                throw OAuthTokenExchangeFailed::invalidVerifier();
            }

            $expectedChallenge = rtrim(strtr(base64_encode(hash('sha256', $data->codeVerifier, true)), '+/', '-_'), '=');

            if (! hash_equals($record->codeChallenge, $expectedChallenge)) {
                $this->recordFailure($data, 'pkce_verifier_mismatch');
                throw OAuthTokenExchangeFailed::invalidVerifier();
            }
        }

        $user = $this->userSource->findById($record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->recordFailure($data, 'user_not_active');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $this->codeStore->markUsed($record->codeId, $now);

        $accessToken = $this->jwtIdentity->issue(
            user         : $user,
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $client->clientId,
            scopes       : $record->scopes,
            senderConstraint: $data->senderConstraint
        );
        $refreshToken = $this->refreshTokenStore->issue(
            userId       : $user->getId(),
            expiresAt    : $now->modify('+30 days'),
            mfaVerifiedAt: $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId     : $client->clientId,
            scopes       : $record->scopes,
            senderConstraint: $data->senderConstraint
        );
        $idToken = null;

        if (in_array('openid', $record->scopes, true)) {
            if ($this->oidcProvider === null) {
                $this->recordFailure($data, 'oidc_provider_not_configured');
                throw OAuthTokenExchangeFailed::invalidGrant();
            }

            $idToken = $this->oidcProvider->issueIdToken(
                user              : $user,
                clientId          : $client->clientId,
                scopes            : $record->scopes,
                nonce             : $record->nonce,
                authenticatedAt   : $record->mfaVerifiedAt,
                phishingResistant : $record->phishingResistant
            )->token;
        }

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.authorization_code.exchanged',
            occurredAt: $now,
            context   : [
                'client_id' => $client->clientId,
                'user_id' => $user->getId()->value,
                'code_id' => $record->codeId,
                'scope' => implode(' ', $record->scopes),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));

        return new OAuthTokenGrant(
            accessToken          : $accessToken->token,
            accessTokenExpiresAt : $accessToken->expiresAt,
            refreshToken         : $refreshToken->token,
            idToken              : $idToken,
            clientId             : $client->clientId,
            userId               : $user->getId()->value,
            scopes               : $record->scopes,
            tokenType            : $data->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint     : $data->senderConstraint
        );
    }

    private function recordFailure(
        ExchangeAuthorizationCodeData $data,
        string $reason,
        bool $suspicious = false
    ) : void
    {
        $name = $suspicious
            ? 'auth.oauth.authorization_code.reuse_detected'
            : 'auth.oauth.authorization_code.exchange.failed';

        $this->auditLog->record(new AuditEvent(
            name      : $name,
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'redirect_uri' => $data->redirectUri,
                'reason' => $reason,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
