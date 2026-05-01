<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CheckHealth;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport\FederationHealthCheckInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class CheckFederationConnectionHealth
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private FederationHealthCheckInterface $runtime, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     */
    public function execute(string $connectionId) : FederationConnectionHealth
    {
        $connection = $this->connectionStore->find(connectionId: $connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        $health  = $this->runtime->checkHealth(connection: $connection);
        $updated = $connection->withHealth(health: $health, checkedAt: $this->clock->now());
        $this->connectionStore->save(connection: $updated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.health.checked',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $updated->connectionId,
                                                           'tenant'        => $updated->tenantSlug,
                                                           'health'        => $health->value,
                                                       ],
                                       ));

        return $health;
    }
}
