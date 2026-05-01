<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\RegisterConnection;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionHealth;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\GroupRoleMappingValidator;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\FederationFailed;
use Random\RandomException;

final readonly class RegisterFederationConnection
{
    public function __construct(private FederationConnectionStoreInterface $federationConnectionStore, private GroupRoleMappingValidator $groupRoleMappingValidator, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws FederationFailed
     * @throws RandomException
     */
    public function execute(RegisterFederationConnectionData $registerFederationConnectionData) : FederationConnection
    {
        $domain   = strtolower(string: trim(string: $registerFederationConnectionData->domain));
        $existing = $this->federationConnectionStore->findByDomain(domain: $domain);

        if ($existing instanceof FederationConnection) {
            throw FederationFailed::domainConflict();
        }

        if (! $this->groupRoleMappingValidator->isValid(groupRoleMap: $registerFederationConnectionData->groupRoleMap)) {
            throw FederationFailed::invalidGroupRoleMapping();
        }

        if ($registerFederationConnectionData->breakGlassAllowed && ! $registerFederationConnectionData->ssoOnly) {
            throw FederationFailed::invalidBreakGlassPolicy();
        }

        $federationConnection = new FederationConnection(
            connectionId           : 'fed_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug             : trim(string: $registerFederationConnectionData->tenantSlug),
            name                   : trim(string: $registerFederationConnectionData->name),
            provider               : $registerFederationConnectionData->provider,
            domain                 : $domain,
            ssoOnly                : $registerFederationConnectionData->ssoOnly,
            groupRoleMap           : $registerFederationConnectionData->groupRoleMap,
            metadataUrl            : $registerFederationConnectionData->metadataUrl,
            domainVerificationToken: bin2hex(string: random_bytes(length: 16)),
            breakGlassAllowed      : $registerFederationConnectionData->breakGlassAllowed,
            health                 : FederationConnectionHealth::UNKNOWN,
        );

        $this->federationConnectionStore->save(connection: $federationConnection);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.federation.connection.registered',
            occurredAt: $this->clock->now(),
            context   : [
                            'connection_id'       => $federationConnection->connectionId,
                            'tenant'              => $federationConnection->tenantSlug,
                            'domain'              => $federationConnection->domain,
                            'provider'            => $federationConnection->provider->value,
                            'break_glass_allowed' => $federationConnection->breakGlassAllowed ? 1 : 0,
            ],
        ));

        return $federationConnection;
    }
}
