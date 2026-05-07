<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraintType;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use SensitiveParameter;

final readonly class ExchangeClientCredentials
{
    public function __construct(
        private OAuthClientRegistryInterface $oAuthClientRegistry,
        #[SensitiveParameter]
        private JwtIdentityInterface         $jwtIdentity,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(ExchangeClientCredentialsData $exchangeClientCredentialsData) : OAuthTokenGrant
    {
        $client = $this->oAuthClientRegistry->find(clientId: $exchangeClientCredentialsData->clientId);

        if (! $client instanceof OAuthClient || ! $this->oAuthClientRegistry->verifySecret(clientId: $exchangeClientCredentialsData->clientId, plainTextSecret: $exchangeClientCredentialsData->clientSecret)) {
            $this->recordFailure(reason: 'client_authentication_failed', data: $exchangeClientCredentialsData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::CLIENT_CREDENTIALS) || ! $client->workloadIdentity) {
            $this->recordFailure(reason: 'client_credentials_not_allowed', data: $exchangeClientCredentialsData);

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $scopes   = $this->normalizeScopes(scopes: $exchangeClientCredentialsData->scopes);
        $audience = $this->normalizeAudience(audience: $exchangeClientCredentialsData->audience);

        if (! $client->allowsScopes(scopes: $scopes)) {
            $this->recordFailure(reason: 'scope_mismatch', data: $exchangeClientCredentialsData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudience(audience: $audience)) {
            $this->recordFailure(reason: 'audience_mismatch', data: $exchangeClientCredentialsData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudienceScopes(audience: $audience, scopes: $scopes)) {
            $this->recordFailure(reason: 'scope_boundary_mismatch', data: $exchangeClientCredentialsData);

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if ($client->requiredSenderConstraint instanceof OAuthSenderConstraintType && (! $exchangeClientCredentialsData->senderConstraint instanceof OAuthSenderConstraint || $exchangeClientCredentialsData->senderConstraint->type !== $client->requiredSenderConstraint)) {
            $this->recordFailure(reason: 'sender_constraint_missing_or_wrong_type', data: $exchangeClientCredentialsData);
            throw OAuthTokenExchangeFailed::invalidSenderConstraint();
        }

        $subject     = 'client:' . $client->clientId;
        $issuedToken = $this->jwtIdentity->issueWorkloadToken(
            subject         : $subject,
            clientId        : $client->clientId,
            scopes          : $scopes,
            audience        : $audience,
            senderConstraint: $exchangeClientCredentialsData->senderConstraint,
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.client_credentials.exchanged',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'  => $client->clientId,
                                                           'scope'      => implode(separator: ' ', array: $scopes),
                                                           'audience'   => $audience,
                                                           'ip_address' => $exchangeClientCredentialsData->ipAddress,
                                                           'user_agent' => $exchangeClientCredentialsData->userAgent,
                                                       ],
                                       ));

        return new OAuthTokenGrant(
            accessToken         : $issuedToken->token,
            accessTokenExpiresAt: $issuedToken->expiresAt,
            refreshToken        : null,
            idToken             : null,
            clientId            : $client->clientId,
            userId              : null,
            scopes              : $scopes,
            tokenType           : $exchangeClientCredentialsData->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint    : $exchangeClientCredentialsData->senderConstraint,
            subject             : $subject,
            audience            : $audience,
            workloadIdentity    : true,
        );
    }

    private function recordFailure(ExchangeClientCredentialsData $exchangeClientCredentialsData, string $reason) : void
    {
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.client_credentials.failed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'  => $exchangeClientCredentialsData->clientId,
                                                           'reason'     => $reason,
                                                           'audience'   => $this->normalizeAudience(audience: $exchangeClientCredentialsData->audience),
                                                           'ip_address' => $exchangeClientCredentialsData->ipAddress,
                                                           'user_agent' => $exchangeClientCredentialsData->userAgent,
                                                       ],
                                       ));
    }

    private function normalizeAudience(?string $audience) : ?string
    {
        $normalized = trim(string: (string) $audience);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim(string: $scope);
            if ($value === '') {
                continue;
            }

            if (in_array(needle: $value, haystack: $normalized, strict: true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort(array: $normalized);

        return $normalized;
    }
}
