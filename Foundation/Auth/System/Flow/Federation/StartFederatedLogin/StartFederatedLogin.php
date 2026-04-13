<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\StartFederatedLogin;

use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class StartFederatedLogin
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private FederationRuntimeInterface $runtime,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

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

        if ($connection->health === \Avax\Auth\System\Capability\Federation\FederationConnectionHealth::UNAVAILABLE) {
            throw FederationFailed::connectionUnavailable();
        }

        $started = $this->runtime->startLogin(connection: $connection, redirectUri: $data->redirectUri, state: $data->state);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.federation.login.started',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $connection->connectionId,
                'tenant' => $connection->tenantSlug,
            ]
        ));

        return $started;
    }
}
