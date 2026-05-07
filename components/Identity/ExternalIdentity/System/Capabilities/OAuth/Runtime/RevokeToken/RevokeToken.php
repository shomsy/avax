<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\RefreshTokenRecord;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedWorkloadToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

final readonly class RevokeToken
{
    public function __construct(
        private OAuthClientRegistryInterface $oAuthClientRegistry,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface   $refreshTokenStore,
        #[SensitiveParameter]
        private JwtIdentityInterface         $jwtIdentity,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(RevokeTokenData $revokeTokenData) : void
    {
        $client = $this->oAuthClientRegistry->find(clientId: $revokeTokenData->clientId);

        if (! $client instanceof OAuthClient || ! $this->oAuthClientRegistry->verifySecret(clientId: $revokeTokenData->clientId, plainTextSecret: $revokeTokenData->clientSecret)) {
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $revoked = false;

        if ($revokeTokenData->tokenTypeHint !== 'access_token') {
            $refreshRecord = $this->refreshTokenStore->find(plainToken: $revokeTokenData->token);

            if ($refreshRecord instanceof RefreshTokenRecord && $refreshRecord->clientId === $revokeTokenData->clientId) {
                $this->refreshTokenStore->revokeFamily(familyId: $refreshRecord->familyId);
                $revoked = true;
            }
        }

        if ($revokeTokenData->tokenTypeHint !== 'refresh_token') {
            $access = $this->jwtIdentity->resolve(token: $revokeTokenData->token);

            if ($access instanceof ResolvedToken && $access->clientId === $revokeTokenData->clientId) {
                $this->jwtIdentity->revoke(tokenId: $access->tokenId, expiresAt: $access->expiresAt);
                $revoked = true;
            }

            $workload = $this->jwtIdentity->resolveWorkloadToken(token: $revokeTokenData->token);

            if ($workload instanceof ResolvedWorkloadToken && $workload->clientId === $revokeTokenData->clientId) {
                $this->jwtIdentity->revoke(tokenId: $workload->tokenId, expiresAt: $workload->expiresAt);
                $revoked = true;
            }
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.token.revoked',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'       => $revokeTokenData->clientId,
                                                           'token_type_hint' => $revokeTokenData->tokenTypeHint,
                                                           'revoked'         => $revoked ? 1 : 0,
                                                           'ip_address'      => $revokeTokenData->ipAddress,
                                                           'user_agent'      => $revokeTokenData->userAgent,
                                                       ],
                                       ));
    }
}
