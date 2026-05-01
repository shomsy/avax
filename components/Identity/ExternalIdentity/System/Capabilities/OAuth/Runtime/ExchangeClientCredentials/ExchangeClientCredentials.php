<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeClientCredentials;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthGrantType;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenGrant;
use SensitiveParameter;

final readonly class ExchangeClientCredentials
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        #[SensitiveParameter]
        private JwtIdentityInterface $jwtIdentity,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(ExchangeClientCredentialsData $data): OAuthTokenGrant
    {
        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret(clientId: $data->clientId, plainTextSecret: $data->clientSecret)) {
            $this->recordFailure(data: $data, reason: 'client_authentication_failed');

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(grantType: OAuthGrantType::CLIENT_CREDENTIALS) || ! $client->workloadIdentity) {
            $this->recordFailure(data: $data, reason: 'client_credentials_not_allowed');

            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $scopes = $this->normalizeScopes(scopes: $data->scopes);
        $audience = $this->normalizeAudience(audience: $data->audience);

        if (! $client->allowsScopes(scopes: $scopes)) {
            $this->recordFailure(data: $data, reason: 'scope_mismatch');

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudience(audience: $audience)) {
            $this->recordFailure(data: $data, reason: 'audience_mismatch');

            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudienceScopes(audience: $audience, scopes: $scopes)) {
            $this->recordFailure(data: $data, reason: 'scope_boundary_mismatch');

            throw OAuthTokenExchangeFailed::invalidGrant();
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

        $subject = 'client:' . $client->clientId;
        $issued  = $this->jwtIdentity->issueWorkloadToken(
            subject         : $subject,
            clientId        : $client->clientId,
            scopes          : $scopes,
            senderConstraint: $data->senderConstraint,
            audience        : $audience,
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client_credentials.exchanged',
            occurredAt: $this->clock->now(),
            context   : [
                            'client_id' => $client->clientId,
                            'scope'     => implode(separator: ' ', array: $scopes),
                            'audience'  => $audience,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));

        return new OAuthTokenGrant(
            accessToken         : $issued->token,
            accessTokenExpiresAt: $issued->expiresAt,
            refreshToken        : null,
            idToken             : null,
            clientId            : $client->clientId,
            userId              : null,
            scopes              : $scopes,
            tokenType           : $data->senderConstraint?->type->value === 'dpop' ? 'DPoP' : 'Bearer',
            senderConstraint    : $data->senderConstraint,
            subject             : $subject,
            audience            : $audience,
            workloadIdentity    : true,
        );
    }

    private function recordFailure(ExchangeClientCredentialsData $data, string $reason): void
    {
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client_credentials.failed',
            occurredAt: $this->clock->now(),
            context   : [
                            'client_id' => $data->clientId,
                            'reason'    => $reason,
                            'audience'  => $this->normalizeAudience(audience: $data->audience),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));
    }

    private function normalizeAudience(?string $audience): ?string
    {
        $normalized = trim(string: (string) $audience);

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param list<string> $scopes
     *
     * @return list<string>
     */
    private function normalizeScopes(array $scopes): array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim(string: $scope);

            if ($value === '' || in_array(needle: $value, haystack: $normalized, strict: true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort(array: $normalized);

        return $normalized;
    }
}
