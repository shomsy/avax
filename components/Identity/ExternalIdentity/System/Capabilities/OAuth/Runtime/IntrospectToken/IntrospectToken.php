<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\OAuthTokenExchangeFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class IntrospectToken
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        #[SensitiveParameter]
        private JwtIdentityInterface         $jwtIdentity,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock,
    ) {}

    /**
     * @throws OAuthTokenExchangeFailed
     */
    public function execute(IntrospectTokenData $data) : TokenIntrospection
    {
        $client = $this->clientRegistry->find(clientId: $data->clientId);

        if ($client === null || ! $this->clientRegistry->verifySecret(clientId: $data->clientId, plainTextSecret: $data->clientSecret)) {
            throw OAuthTokenExchangeFailed::invalidClient();
        }

        $resolved = $this->jwtIdentity->resolve(token: $data->token);

        if ($resolved !== null && $resolved->clientId === $data->clientId) {
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
                token           : $data->token,
                expectedAudience: $data->expectedAudience,
                expectedIssuer  : $data->expectedIssuer,
            );

            if ($workload === null || $workload->clientId !== $data->clientId) {
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
                                                           'client_id' => $data->clientId,
                                                           'active'    => $result->active ? 1 : 0,
                                                       ],
                                       ));

        return $result;
    }
}
