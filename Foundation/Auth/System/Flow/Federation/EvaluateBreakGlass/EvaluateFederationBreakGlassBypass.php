<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\EvaluateBreakGlass;

use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class EvaluateFederationBreakGlassBypass
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws FederationFailed
     */
    public function execute(string $connectionId) : bool
    {
        $connection = $this->connectionStore->find(connectionId: $connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        $allowed = $connection->breakGlassAllowed
            && in_array($connection->health, [FederationConnectionHealth::DEGRADED, FederationConnectionHealth::UNAVAILABLE], true);

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
            ]
        ));

        return $allowed;
    }
}
