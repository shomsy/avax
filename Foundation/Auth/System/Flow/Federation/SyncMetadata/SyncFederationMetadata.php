<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\SyncMetadata;

use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationMetadataRuntimeInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class SyncFederationMetadata
{
    private Clock                              $clock;
    private AuditLogInterface                  $auditLog;
    private FederationMetadataRuntimeInterface $runtime;
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore,
        FederationMetadataRuntimeInterface $runtime,
        AuditLogInterface                  $auditLog,
        Clock                              $clock
    )
    {
        $this->connectionStore = $connectionStore;
        $this->runtime         = $runtime;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
    }

    /**
     * @throws FederationFailed
     * @throws \JsonException
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
