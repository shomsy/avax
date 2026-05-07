<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedToken;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Record\ResolvedWorkloadToken;
use SensitiveParameter;

final readonly class IntrospectToken
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
    public function execute(IntrospectTokenData $introspectTokenData) : TokenIntrospection
    {
        $client = $this->oAuthClientRegistry->find(clientId: $introspectTokenData->clientId);

        if (! $client instanceof OAuthClient || ! $this->oAuthClientRegistry->verifySecret(clientId: $introspectTokenData->clientId, plainTextSecret: $introspectTokenData->clientSecret)) {
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $resolved = $this->jwtIdentity->resolve(token: $introspectTokenData->token);

        if ($resolved instanceof ResolvedToken && $resolved->clientId === $introspectTokenData->clientId) {
            $result = new TokenIntrospection(
                active           : true,
                clientId         : $resolved->clientId,
                userId           : $resolved->user->getId()->value,
                scopes           : $resolved->scopes,
                expiresAt        : $resolved->expiresAt,
                mfaVerifiedAt    : $resolved->mfaVerifiedAt,
                phishingResistant: $resolved->phishingResistant,
                senderConstraint : $resolved->senderConstraint,
            );
        } else {
            $workload = $this->jwtIdentity->resolveWorkloadToken(
                token           : $introspectTokenData->token,
                expectedAudience: $introspectTokenData->expectedAudience,
                expectedIssuer  : $introspectTokenData->expectedIssuer,
            );

            if (! $workload instanceof ResolvedWorkloadToken || $workload->clientId !== $introspectTokenData->clientId) {
                $result = TokenIntrospection::inactive();
            } else {
                $result = new TokenIntrospection(
                    active          : true,
                    clientId        : $workload->clientId,
                    scopes          : $workload->scopes,
                    expiresAt       : $workload->expiresAt,
                    senderConstraint: $workload->senderConstraint,
                    subject         : $workload->subject,
                    audience        : $workload->audience,
                    issuer          : $workload->issuer,
                    workloadIdentity: true,
                );
            }
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.token.introspected',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id' => $introspectTokenData->clientId,
                                                           'active'    => $result->active ? 1 : 0,
                                                       ],
                                       ));

        return $result;
    }
}
