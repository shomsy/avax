<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\CheckHealth;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\Federation\FederationHealthCheckInterface;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;

final readonly class CheckFederationConnectionHealth
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private FederationHealthCheckInterface $federationHealthCheck, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     */
    public function execute(string $connectionId) : FederationConnectionHealth
    {
        $connection = $this->federationConnectionStore->find(connectionId: $connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        $federationConnectionHealth = $this->federationHealthCheck->checkHealth(connection: $connection);
        $federationConnection       = $connection->withHealth(checkedAt: $this->clock->now(), health: $federationConnectionHealth);
        $this->federationConnectionStore->save(connection: $federationConnection);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.health.checked',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $federationConnection->connectionId,
                                                           'tenant'        => $federationConnection->tenantSlug,
                                                           'health'        => $federationConnectionHealth->value,
                                                       ],
                                       ));

        return $federationConnectionHealth;
    }
}
