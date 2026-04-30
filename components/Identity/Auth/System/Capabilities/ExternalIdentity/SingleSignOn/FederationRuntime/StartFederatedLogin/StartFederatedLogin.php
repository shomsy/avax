<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\StartFederatedLogin;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationRuntimeInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;
use Avax\Components\Identity\Auth\System\Foundation\Clock;

final readonly class StartFederatedLogin
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private FederationRuntimeInterface $runtime, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     */
    public function execute(StartFederatedLoginData $data) : StartedFederatedLogin
    {
        $connection = $this->connectionStore->find(connectionId: $data->connectionId);

        if ($connection === null) {
            throw FederationFailed::notFound();
        }

        if (! $connection->isDomainVerified()) {
            throw FederationFailed::domainNotVerified();
        }

        if ($connection->health === FederationConnectionHealth::UNAVAILABLE) {
            throw FederationFailed::connectionUnavailable();
        }

        $started = $this->runtime->startLogin(connection: $connection, redirectUri: $data->redirectUri, state: $data->state);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.login.started',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id' => $connection->connectionId,
                                                           'tenant'        => $connection->tenantSlug,
                                                       ],
                                       ));

        return $started;
    }
}
