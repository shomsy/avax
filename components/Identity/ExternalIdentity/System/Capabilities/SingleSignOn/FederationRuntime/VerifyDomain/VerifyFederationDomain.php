<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\VerifyDomain;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;

final readonly class VerifyFederationDomain
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     */
    public function execute(VerifyFederationDomainData $verifyFederationDomainData) : FederationConnection
    {
        $connection = $this->federationConnectionStore->find(connectionId: $verifyFederationDomainData->connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        if (
            $connection->domainVerificationToken === null
            || ! hash_equals(known_string: $connection->domainVerificationToken, user_string: trim(string: $verifyFederationDomainData->verificationToken))
        ) {
            throw FederationFailed::invalidDomainVerificationToken();
        }

        $federationConnection = $connection->withVerifiedDomain(verifiedAt: $this->clock->now());
        $this->federationConnectionStore->save(connection: $federationConnection);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.domain.verified',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $federationConnection->connectionId,
                                                           'tenant'        => $federationConnection->tenantSlug,
                                                           'domain'        => $federationConnection->domain,
                                                       ],
                                       ));

        return $federationConnection;
    }
}
