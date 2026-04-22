<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\SyncMetadata;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationMetadataRuntimeInterface;
use Avax\Auth\System\Foundation\Clock;
use JsonException;

final readonly class SyncFederationMetadata
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private FederationMetadataRuntimeInterface $runtime, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    /**
     * @throws FederationFailed
     * @throws JsonException
     */
    public function execute(string $connectionId) : FederationConnection
    {
        $connection = $this->connectionStore->find(connectionId: $connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        if ($connection->metadataUrl === null || trim($connection->metadataUrl) === '') {
            throw FederationFailed::metadataUrlMissing();
        }

        $metadata = $this->runtime->readMetadata(connection: $connection);
        $synced   = $connection->withMetadata(
            metadataIssuer: $metadata->issuer,
            metadataHash  : hash('sha256', json_encode([
                                                           'issuer' => $metadata->issuer,
                                                                                                                                                                          'single_sign_on_url' => $metadata->singleSignOnUrl,
                                                           'claims' => $metadata->claims,
                                                       ], JSON_THROW_ON_ERROR)),
            syncedAt      : $this->clock->now()
        );
        $this->connectionStore->save(connection: $synced);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.metadata.synced',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id'   => $synced->connectionId,
                                                           'tenant'          => $synced->tenantSlug,
                                                           'metadata_issuer' => $synced->metadataIssuer,
                                                       ]
                                       ));

        return $synced;
    }
}
