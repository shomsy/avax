<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\RegisterConnection;

use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class RegisterFederationConnection
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(RegisterFederationConnectionData $data) : FederationConnection
    {
        $connection = new FederationConnection(
            connectionId: 'fed_' . bin2hex(random_bytes(12)),
            tenantSlug  : trim($data->tenantSlug),
            name        : trim($data->name),
            provider    : $data->provider,
            domain      : strtolower(trim($data->domain)),
            ssoOnly     : $data->ssoOnly,
            groupRoleMap: $data->groupRoleMap
        );

        $this->connectionStore->save($connection);
        $this->auditLog->record(new AuditEvent(
            name      : 'auth.federation.connection.registered',
            occurredAt: $this->clock->now(),
            context   : [
                'connection_id' => $connection->connectionId,
                'tenant' => $connection->tenantSlug,
                'domain' => $connection->domain,
                'provider' => $connection->provider->value,
            ]
        ));

        return $connection;
    }
}
