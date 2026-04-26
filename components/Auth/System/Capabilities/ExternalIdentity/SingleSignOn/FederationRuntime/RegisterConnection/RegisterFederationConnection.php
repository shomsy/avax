<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection;

use components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\FederationFailed;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\GroupRoleMappingValidator;
use components\Auth\System\Foundation\Clock;
use Random\RandomException;

final readonly class RegisterFederationConnection
{
    public function __construct(private FederationConnectionStoreInterface $connectionStore, private GroupRoleMappingValidator $groupRoleMappingValidator, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     * @throws RandomException
     */
    public function execute(RegisterFederationConnectionData $data) : FederationConnection
    {
        $domain   = strtolower(string: trim(string: $data->domain));
        $existing = $this->connectionStore->findByDomain(domain: $domain);

        if ($existing !== null) {
            throw FederationFailed::domainConflict();
        }

        if (! $this->groupRoleMappingValidator->isValid(groupRoleMap: $data->groupRoleMap)) {
            throw FederationFailed::invalidGroupRoleMapping();
        }

        if ($data->breakGlassAllowed && ! $data->ssoOnly) {
            throw FederationFailed::invalidBreakGlassPolicy();
        }

        $connection = new FederationConnection(
            connectionId           : 'fed_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug             : trim(string: $data->tenantSlug),
            name                   : trim(string: $data->name),
            provider               : $data->provider,
            domain                 : $domain,
            ssoOnly                : $data->ssoOnly,
            groupRoleMap           : $data->groupRoleMap,
            metadataUrl            : $data->metadataUrl,
            domainVerificationToken: bin2hex(string: random_bytes(length: 16)),
            health                 : FederationConnectionHealth::UNKNOWN,
            breakGlassAllowed      : $data->breakGlassAllowed
        );

        $this->connectionStore->save(connection: $connection);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.federation.connection.registered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'connection_id'       => $connection->connectionId,
                                                           'tenant'              => $connection->tenantSlug,
                                                           'domain'              => $connection->domain,
                                                           'provider'            => $connection->provider->value,
                                                           'break_glass_allowed' => $connection->breakGlassAllowed ? 1 : 0,
                                                       ]
                                       ));

        return $connection;
    }
}
