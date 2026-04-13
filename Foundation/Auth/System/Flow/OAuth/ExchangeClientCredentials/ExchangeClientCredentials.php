<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeClientCredentials;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthGrantType;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\OAuth\OAuthTokenGrant;
use Avax\Auth\System\Foundation\Clock;

final readonly class ExchangeClientCredentials
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        private JwtIdentityInterface $jwtIdentity,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(ExchangeClientCredentialsData $data) : OAuthTokenGrant
    {
        $client = $this->clientRegistry->find($data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret($data->clientId, $data->clientSecret)) {
            $this->recordFailure($data, 'client_authentication_failed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        if (! $client->allowsGrantType(OAuthGrantType::CLIENT_CREDENTIALS) || ! $client->workloadIdentity) {
            $this->recordFailure($data, 'client_credentials_not_allowed');
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $scopes = $this->normalizeScopes($data->scopes);
        $audience = $this->normalizeAudience($data->audience);

        if (! $client->allowsScopes($scopes)) {
            $this->recordFailure($data, 'scope_mismatch');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudience($audience)) {
            $this->recordFailure($data, 'audience_mismatch');
            throw OAuthTokenExchangeFailed::invalidGrant();
        }

        if (! $client->allowsAudienceScopes($audience, $scopes)) {
            $this->recordFailure($data, 'scope_boundary_mismatch');
            throw OAuthTokenExchangeFailed::invalidGrant();
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

        $subject  = 'client:' . $client->clientId;
        $issued   = $this->jwtIdentity->issueWorkloadToken(
            subject         : $subject,
            clientId        : $client->clientId,
            scopes          : $scopes,
            senderConstraint: $data->senderConstraint,
            audience        : $audience
        );

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.client_credentials.exchanged',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $client->clientId,
                'scope' => implode(' ', $scopes),
                'audience' => $audience,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
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
            workloadIdentity    : true
        );
    }

    /**
     * @param list<string> $scopes
     * @return list<string>
     */
    private function normalizeScopes(array $scopes) : array
    {
        $normalized = [];

        foreach ($scopes as $scope) {
            $value = trim($scope);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        sort($normalized);

        return $normalized;
    }

    private function normalizeAudience(string|null $audience) : string|null
    {
        $normalized = trim((string) $audience);

        return $normalized !== '' ? $normalized : null;
    }

    private function recordFailure(ExchangeClientCredentialsData $data, string $reason) : void
    {
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.client_credentials.failed',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'reason' => $reason,
                'audience' => $this->normalizeAudience($data->audience),
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
