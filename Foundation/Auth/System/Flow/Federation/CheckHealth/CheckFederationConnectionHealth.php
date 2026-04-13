<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\CheckHealth;

use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationHealthCheckInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class CheckFederationConnectionHealth
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private FederationHealthCheckInterface $runtime,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws FederationFailed
     */
    public function execute(string $connectionId) : FederationConnectionHealth
    {
        $connection = $this->connectionStore->find(connectionId: $connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        $health = $this->runtime->checkHealth(connection: $connection);
        $updated = $connection->withHealth(health: $health, checkedAt: $this->clock->now());
        $this->connectionStore->save(connection: $updated);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.federation.health.checked',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $updated->connectionId,
                'tenant' => $updated->tenantSlug,
                'health' => $health->value,
            ]
        ));

        return $health;
    }
}
