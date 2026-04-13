<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\RegisterConnection;

use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Federation\GroupRoleMappingValidator;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;

final readonly class RegisterFederationConnection
{
    public function __construct(
        private FederationConnectionStoreInterface $connectionStore,
        private GroupRoleMappingValidator $groupRoleMappingValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws FederationFailed
     */
    public function execute(RegisterFederationConnectionData $data) : FederationConnection
    {
        $domain = strtolower(trim($data->domain));
        $existing = $this->connectionStore->findByDomain($domain);

        if ($existing !== null) {
            throw FederationFailed::domainConflict();
        }

        if (! $this->groupRoleMappingValidator->isValid($data->groupRoleMap)) {
            throw FederationFailed::invalidGroupRoleMapping();
        }

        if ($data->breakGlassAllowed && ! $data->ssoOnly) {
            throw FederationFailed::invalidBreakGlassPolicy();
        }

        $connection = new FederationConnection(
            connectionId: 'fed_' . bin2hex(random_bytes(12)),
            tenantSlug  : trim($data->tenantSlug),
            name        : trim($data->name),
            provider    : $data->provider,
            domain      : $domain,
            ssoOnly     : $data->ssoOnly,
            groupRoleMap: $data->groupRoleMap,
            metadataUrl : $data->metadataUrl,
            domainVerificationToken: bin2hex(random_bytes(16)),
            health      : FederationConnectionHealth::UNKNOWN,
            breakGlassAllowed: $data->breakGlassAllowed
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
                'break_glass_allowed' => $connection->breakGlassAllowed ? 1 : 0,
            ]
        ));

        return $connection;
    }
}
