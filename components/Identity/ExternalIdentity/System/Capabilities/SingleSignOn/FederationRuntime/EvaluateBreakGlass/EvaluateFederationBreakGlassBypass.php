<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\EvaluateBreakGlass;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;

final readonly class EvaluateFederationBreakGlassBypass
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    /**
     * @throws FederationFailed
     */
    public function execute(string $connectionId): bool
    {
        $connection = $this->federationConnectionStore->find(connectionId: $connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        $allowed = $connection->breakGlassAllowed
            && in_array(needle: $connection->health, haystack: [FederationConnectionHealth::DEGRADED, FederationConnectionHealth::UNAVAILABLE], strict: true);

        $this->auditLog->record(event: new AuditEvent(
            name      : $allowed
                            ? 'auth.federation.break_glass.allowed'
                            : 'auth.federation.break_glass.denied',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $connection->connectionId,
                'tenant' => $connection->tenantSlug,
                'health' => $connection->health->value,
                'break_glass_allowed' => $connection->breakGlassAllowed ? 1 : 0,
            ],
        ));

        return $allowed;
    }
}
