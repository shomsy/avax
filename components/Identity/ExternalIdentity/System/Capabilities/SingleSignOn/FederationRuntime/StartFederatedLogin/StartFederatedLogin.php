<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\StartFederatedLogin;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationRuntimeInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\StartedFederatedLogin;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;

final readonly class StartFederatedLogin
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private FederationRuntimeInterface $federationRuntime, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     */
    public function execute(StartFederatedLoginData $startFederatedLoginData) : StartedFederatedLogin
    {
        $connection = $this->federationConnectionStore->find(connectionId: $startFederatedLoginData->connectionId);

        if (! $connection instanceof FederationConnection) {
            throw FederationFailed::notFound();
        }

        if (! $connection->isDomainVerified()) {
            throw FederationFailed::domainNotVerified();
        }

        if ($connection->health === FederationConnectionHealth::UNAVAILABLE) {
            throw FederationFailed::connectionUnavailable();
        }

        $startedFederatedLogin = $this->federationRuntime->startLogin(redirectUri: $startFederatedLoginData->redirectUri, state: $startFederatedLoginData->state, connection: $connection);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.federation.login.started',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $connection->connectionId,
                'tenant' => $connection->tenantSlug,
            ],
        ));

        return $startedFederatedLogin;
    }
}
