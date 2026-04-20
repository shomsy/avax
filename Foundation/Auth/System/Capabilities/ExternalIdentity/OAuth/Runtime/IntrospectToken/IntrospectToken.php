<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\IntrospectToken;

use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\OAuth\OAuthTokenExchangeFailed;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

final readonly class IntrospectToken
{
    private Clock                        $clock;
    private AuditLogInterface            $auditLog;
    private JwtIdentityInterface         $jwtIdentity;
    private OAuthClientRegistryInterface $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface               $clientRegistry,
        #[SensitiveParameter] JwtIdentityInterface $jwtIdentity,
        AuditLogInterface                          $auditLog,
        Clock                                      $clock
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->jwtIdentity    = $jwtIdentity;
        $this->auditLog       = $auditLog;
        $this->clock          = $clock;
    }

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
                senderConstraint : $resolved->senderConstraint
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
                    active          : true,
                    clientId        : $workload->clientId,
                    scopes          : $workload->scopes,
                    expiresAt       : $workload->expiresAt,
                    senderConstraint: $workload->senderConstraint,
                    subject         : $workload->subject,
                    audience        : $workload->audience,
                    issuer          : $workload->issuer,
                    workloadIdentity: true
                );
            }
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.token.introspected',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id' => $data->clientId,
                                                           'active'    => $result->active ? 1 : 0,
                                                       ]
                                       ));

        return $result;
    }
}
