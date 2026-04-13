<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\IntrospectToken;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class IntrospectToken
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
    public function execute(IntrospectTokenData $data) : TokenIntrospection
    {
        $client = $this->clientRegistry->find($data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret($data->clientId, $data->clientSecret)) {
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $resolved = $this->jwtIdentity->resolve($data->token);

        if ($resolved !== null && $resolved->clientId === $data->clientId) {
            $result = new TokenIntrospection(
                active       : true,
                clientId     : $resolved->clientId,
                userId       : $resolved->user->getId()->value,
                scopes       : $resolved->scopes,
                expiresAt    : $resolved->expiresAt,
                mfaVerifiedAt: $resolved->mfaVerifiedAt,
                phishingResistant: $resolved->phishingResistant,
                senderConstraint: $resolved->senderConstraint
            );
        } else {
            $workload = $this->jwtIdentity->resolveWorkloadToken(
                token           : $data->token,
                expectedAudience: $data->expectedAudience,
                expectedIssuer  : $data->expectedIssuer
            );

            if ($workload === null || $workload->clientId !== $data->clientId) {
                $result = TokenIntrospection::inactive();
            } else {
                $result = new TokenIntrospection(
                    active           : true,
                    clientId         : $workload->clientId,
                    scopes           : $workload->scopes,
                    expiresAt        : $workload->expiresAt,
                    senderConstraint : $workload->senderConstraint,
                    subject          : $workload->subject,
                    audience         : $workload->audience,
                    issuer           : $workload->issuer,
                    workloadIdentity : true
                );
            }
        }

        $this->auditLog->record(new AuditEvent(
            name      : 'auth.oauth.token.introspected',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $data->clientId,
                'active' => $result->active ? 1 : 0,
            ]
        ));

        return $result;
    }
}
