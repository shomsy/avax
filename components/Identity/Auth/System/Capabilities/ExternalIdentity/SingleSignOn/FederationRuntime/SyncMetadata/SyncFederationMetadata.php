<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\SyncMetadata;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationMetadataRuntimeInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use JsonException;

final readonly class SyncFederationMetadata
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private FederationMetadataRuntimeInterface $runtime, private AuditLogInterface $auditLog, private Clock $clock) {}

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

        if ($connection->metadataUrl === null || trim(string: $connection->metadataUrl) === '') {
            throw FederationFailed::metadataUrlMissing();
        }

        $metadata = $this->runtime->readMetadata(connection: $connection);
        $synced   = $connection->withMetadata(
            metadataIssuer: $metadata->issuer,
            metadataHash  : hash(algo: 'sha256', data: json_encode(value: [
                                                                              'issuer' => $metadata->issuer,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                          'single_sign_on_url' => $metadata->singleSignOnUrl,
                                                                              'claims' => $metadata->claims,
                                                                          ], flags: JSON_THROW_ON_ERROR)),
            syncedAt      : $this->clock->now(),
        );
        $this->connectionStore->save(connection: $synced);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.metadata.synced',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id'   => $synced->connectionId,
                                                           'tenant'          => $synced->tenantSlug,
                                                           'metadata_issuer' => $synced->metadataIssuer,
                                                       ],
                                       ));

        return $synced;
    }
}
