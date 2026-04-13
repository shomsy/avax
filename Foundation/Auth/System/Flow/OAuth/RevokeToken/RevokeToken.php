<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\RevokeToken;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class RevokeToken
{
    public function __construct(
        private OAuthClientRegistryInterface                     $clientRegistry,
        #[SensitiveParameter] private RefreshTokenStoreInterface $refreshTokenStore,
        #[SensitiveParameter] private JwtIdentityInterface       $jwtIdentity,
        private AuditLogInterface                                $auditLog,
        private Clock                                            $clock
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(RevokeTokenData $data) : void
    {
        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret(clientId: $data->clientId, plainTextSecret: $data->clientSecret)) {
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $revoked = false;

        if ($data->tokenTypeHint !== 'access_token') {
            $refreshRecord = $this->refreshTokenStore->find(plainToken: $data->token);

            if ($refreshRecord !== null && $refreshRecord->clientId === $data->clientId) {
                $this->refreshTokenStore->revokeFamily(familyId: $refreshRecord->familyId);
                $revoked = true;
            }
        }

        if ($data->tokenTypeHint !== 'refresh_token') {
            $access = $this->jwtIdentity->resolve(token: $data->token);

            if ($access !== null && $access->clientId === $data->clientId) {
                $this->jwtIdentity->revoke(tokenId: $access->tokenId, expiresAt: $access->expiresAt);
                $revoked = true;
            }

            $workload = $this->jwtIdentity->resolveWorkloadToken(token: $data->token);

            if ($workload !== null && $workload->clientId === $data->clientId) {
                $this->jwtIdentity->revoke(tokenId: $workload->tokenId, expiresAt: $workload->expiresAt);
                $revoked = true;
            }
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.token.revoked',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'token_type_hint' => $data->tokenTypeHint,
                'revoked' => $revoked ? 1 : 0,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ]
        ));
    }
}
