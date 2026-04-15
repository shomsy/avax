<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\OAuth\AuthorizationCodeStoreInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Capability\OAuth\PkceMethod;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class ExchangeAuthorizationCode
{
    private OidcProviderInterface|null      $oidcProvider;
    private CurrentAuthentication|null      $currentAuthentication;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private RefreshTokenStoreInterface      $refreshTokenStore;
    private JwtIdentityInterface            $jwtIdentity;
    private UserSourceInterface             $userSource;
    private AuthorizationCodeStoreInterface $codeStore;
    private OAuthClientRegistryInterface    $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface                          $clientRegistry,
        #[SensitiveParameter] AuthorizationCodeStoreInterface $codeStore,
        UserSourceInterface                                   $userSource,
        #[SensitiveParameter] JwtIdentityInterface            $jwtIdentity,
        #[SensitiveParameter] RefreshTokenStoreInterface      $refreshTokenStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] CurrentAuthentication|null      $currentAuthentication = null,
        OidcProviderInterface|null                            $oidcProvider = null
    )
    {
        $this->clientRegistry        = $clientRegistry;
        $this->codeStore             = $codeStore;
        $this->userSource            = $userSource;
        $this->jwtIdentity           = $jwtIdentity;
        $this->refreshTokenStore     = $refreshTokenStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->currentAuthentication = $currentAuthentication;
        $this->oidcProvider          = $oidcProvider;
    }

    /**
     * @throws OAuthTokenExchangeFailed
     * @throws \DateMalformedStringException
     */
    public function execute(ExchangeAuthorizationCodeData $data) : OAuthTokenGrant
    {
        $now    = $this->clock->now();
        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret(clientId: $data->clientId, plainTextSecret: $data->clientSecret)) {
            $this->recordFailure(data: $data, reason: 'client_authentication_failed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::AUTHORIZATION_CODE)) {
            $this->recordFailure(data: $data, reason: 'grant_type_not_allowed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $record = $this->codeStore->find(plainCode: $data->code);

        if ($record === null) {
            $this->recordFailure(data: $data, reason: 'grant_not_found');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->wasUsed()) {
            $this->recordFailure(data: $data, reason: 'grant_reused', suspicious: true);
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->isExpiredAt(moment: $now) || $record->clientId !== $data->clientId) {
            $this->recordFailure(data: $data, reason: 'grant_expired_or_mismatch');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($record->redirectUri !== $data->redirectUri) {
            $this->recordFailure(data: $data, reason: 'redirect_uri_mismatch');
            throw OAuthTokenExchangeFailed::invalidRedirectUri();
        }

        if ($client->requiredSenderConstraint !== null) {
            if (
                $data->senderConstraint === null
                || $data->senderConstraint->type !== $client->requiredSenderConstraint
            ) {
                $this->recordFailure(data: $data, reason: 'sender_constraint_missing_or_wrong_type');
                throw OAuthTokenExchangeFailed::invalidSenderConstraint();
            }
        }

        if ($record->codeChallenge !== null) {
            if ($record->codeChallengeMethod !== PkceMethod::S256 || $data->codeVerifier === null) {
                $this->recordFailure(data: $data, reason: 'pkce_verifier_missing');
                throw OAuthTokenExchangeFailed::invalidVerifier();
            }

            $expectedChallenge = rtrim(strtr(base64_encode(hash('sha256', $data->codeVerifier, true)), '+/', '-_'), '=');

            if (! hash_equals($record->codeChallenge, $expectedChallenge)) {
                $this->recordFailure(data: $data, reason: 'pkce_verifier_mismatch');
                throw OAuthTokenExchangeFailed::invalidVerifier();
            }
        }

        $user = $this->userSource->findById(id: $record->userId);

        if ($user === null || ! $user->isActive()) {
            $this->recordFailure(data: $data, reason: 'user_not_active');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        $this->codeStore->markUsed(codeId: $record->codeId, usedAt: $now);

        $refreshToken = $this->refreshTokenStore->issue(
            userId           : $user->getId(),
            expiresAt        : $now->modify(modifier: '+30 days'),
            mfaVerifiedAt    : $record->mfaVerifiedAt,
            phishingResistant: $record->phishingResistant,
            clientId         : $client->clientId,
            scopes           : $record->scopes,
            senderConstraint : $data->senderConstraint
        );
        $accessToken  = $this->jwtIdentity->issue(
            user                : $user,
            mfaVerifiedAt       : $record->mfaVerifiedAt,
            phishingResistant   : $record->phishingResistant,
            clientId            : $client->clientId,
            scopes              : $record->scopes,
            senderConstraint    : $data->senderConstraint,
            refreshTokenFamilyId: $refreshToken->familyId
        );
        $idToken      = null;

        if (in_array('openid', $record->scopes, true)) {
            if ($this->oidcProvider === null) {
                $this->recordFailure(data: $data, reason: 'oidc_provider_not_configured');
                throw OAuthTokenExchangeFailed::invalidGrant();
            }

            $idToken = $this->oidcProvider->issueIdToken(
                user             : $user,
                clientId         : $client->clientId,
                scopes           : $record->scopes,
                nonce            : $record->nonce,
                authenticatedAt  : $record->mfaVerifiedAt,
                sessionId        : $this->currentAuthentication?->read()->sessionId(),
                phishingResistant: $record->phishingResistant
            )->token;
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.authorization_code.exchanged',
                                           occurredAt: $now,
                                           context   : [
                                                           'client_id'  => $client->clientId,
                                                           'user_id'    => $user->getId()->value,
                                                           'code_id'    => $record->codeId,
                                                           'scope'      => implode(' ', $record->scopes),
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));

        return new OAuthTokenGrant(
            accessToken         : $accessToken->token,
            accessTokenExpiresAt: $accessToken->expiresAt,
            refreshToken        : $refreshToken->token,
            idToken             : $idToken,
            clientId            : $client->clientId,
            userId              : $user->getId()->value,
            scopes              : $record->scopes,
            tokenType           : $data->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint    : $data->senderConstraint
        );
    }

    private function recordFailure(
        ExchangeAuthorizationCodeData $data,
        string                        $reason,
        bool                          $suspicious = false
    ) : void
    {
        $name = $suspicious
            ? 'auth.oauth.authorization_code.reuse_detected'
            : 'auth.oauth.authorization_code.exchange.failed';

        $this->auditLog->record(event: new AuditEvent(
                                           name      : $name,
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'    => $data->clientId,
                                                           'redirect_uri' => $data->redirectUri,
                                                           'reason'       => $reason,
                                                           'ip_address'   => $data->ipAddress,
                                                           'user_agent'   => $data->userAgent,
                                                       ]
                                       ));
    }
}
