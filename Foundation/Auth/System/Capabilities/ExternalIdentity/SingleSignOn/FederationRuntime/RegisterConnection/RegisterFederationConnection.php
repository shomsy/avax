<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Federation\RegisterConnection;

use Avax\Auth\System\Capabilities\Federation\FederationConnection;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capabilities\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capabilities\Federation\GroupRoleMappingValidator;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Federation\FederationFailed;
use Avax\Auth\System\Foundation\Clock;
use Random\RandomException;

final readonly class RegisterFederationConnection
{
    private Clock                              $clock;
    private AuditLogInterface                  $auditLog;
    private GroupRoleMappingValidator          $groupRoleMappingValidator;
    private FederationConnectionStoreInterface $connectionStore;

    public function __construct(
        FederationConnectionStoreInterface $connectionStore,
        GroupRoleMappingValidator          $groupRoleMappingValidator,
        AuditLogInterface                  $auditLog,
        Clock                              $clock
    )
    {
        $this->connectionStore           = $connectionStore;
        $this->groupRoleMappingValidator = $groupRoleMappingValidator;
        $this->auditLog                  = $auditLog;
        $this->clock                     = $clock;
    }

    /**
     * @throws FederationFailed
     * @throws RandomException
     * @throws RandomException
     */
    public function execute(RegisterFederationConnectionData $data) : FederationConnection
    {
        $domain   = strtolower(trim($data->domain));
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
            connectionId           : 'fed_' . bin2hex(random_bytes(12)),
            tenantSlug             : trim($data->tenantSlug),
            name                   : trim($data->name),
            provider               : $data->provider,
            domain                 : $domain,
            ssoOnly                : $data->ssoOnly,
            groupRoleMap           : $data->groupRoleMap,
            metadataUrl            : $data->metadataUrl,
            domainVerificationToken: bin2hex(random_bytes(16)),
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
