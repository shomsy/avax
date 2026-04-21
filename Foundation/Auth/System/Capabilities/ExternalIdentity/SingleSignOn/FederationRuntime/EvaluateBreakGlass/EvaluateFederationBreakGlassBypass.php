<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\EvaluateBreakGlass;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class EvaluateFederationBreakGlassBypass
{
    private Clock                              $clock;
    private AuditLogInterface                  $auditLog;
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore,
        AuditLogInterface                  $auditLog,
        Clock                              $clock
    )
    {
        $this->connectionStore = $connectionStore;
        $this->auditLog        = $auditLog;
        $this->clock           = $clock;
    }

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
                                                           'connection_id'       => $connection->connectionId,
                                                           'tenant'              => $connection->tenantSlug,
                                                           'health'              => $connection->health->value,
                                                           'break_glass_allowed' => $connection->breakGlassAllowed ? 1 : 0,
                                                       ]
                                       ));

        return $allowed;
    }
}
