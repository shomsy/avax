<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\SyncMetadata;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;
use JsonException;

final readonly class SyncFederationMetadata
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private FederationMetadataRuntimeInterface $federationMetadataRuntime, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     * @throws JsonException
     */
    public function execute(string $connectionId) : FederationConnection
    {
        $connection = $this->federationConnectionStore->find(connectionId: $connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        if ($connection->metadataUrl === null || trim(string: $connection->metadataUrl) === '') {
            throw FederationFailed::metadataUrlMissing();
        }

        $federationMetadata   = $this->federationMetadataRuntime->readMetadata(connection: $connection);
        $federationConnection = $connection->withMetadata(
            metadataIssuer: $federationMetadata->issuer,
            metadataHash  : hash(algo: 'sha256', data: json_encode(value: [
                                                                              'issuer' => $federationMetadata->issuer,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                          'single_sign_on_url' => $federationMetadata->singleSignOnUrl,
                                                                              'claims' => $federationMetadata->claims,
                                                                          ], flags: JSON_THROW_ON_ERROR)),
            syncedAt      : $this->clock->now(),
        );
        $this->federationConnectionStore->save(connection: $federationConnection);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.metadata.synced',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id'   => $federationConnection->connectionId,
                                                           'tenant'          => $federationConnection->tenantSlug,
                                                           'metadata_issuer' => $federationConnection->metadataIssuer,
                                                       ],
                                       ));

        return $federationConnection;
    }
}
