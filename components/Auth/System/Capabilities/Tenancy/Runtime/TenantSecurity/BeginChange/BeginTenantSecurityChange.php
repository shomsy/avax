<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange;

use components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use components\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use components\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\TenantSecurityFailed;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStatus;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use components\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;
use components\Auth\System\Foundation\Clock;
use JsonException;
use Random\RandomException;

final readonly class BeginTenantSecurityChange
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $configurationStore, private TenantSecurityChangeRequestStoreInterface $changeRequestStore, private FederationConnectionStoreInterface|null $federationConnectionStore, private ScimDirectoryStoreInterface|null $scimDirectoryStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws TenantSecurityFailed
     * @throws RandomException
     */
    public function execute(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        $this->assertKnownReferences(after: $data->after);

        $before        = $this->configurationStore->find(tenantSlug: $data->tenantSlug);
        $after         = new TenantSecurityConfiguration(
            tenantSlug            : $data->tenantSlug,
            federationConnectionId: $data->after->federationConnectionId,
            scimDirectoryId       : $data->after->scimDirectoryId,
            verifiedDomains       : $data->after->verifiedDomains,
            groupRoleMap          : $data->after->groupRoleMap,
            policyProfile         : $data->after->policyProfile,
            rolloutVersion        : $before !== null ? $before->rolloutVersion + 1 : $data->after->rolloutVersion
        );
        $changeRequest = new TenantSecurityChangeRequest(
            changeId   : 'tenant_change_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug : $data->tenantSlug,
            requestedBy: trim(string: $data->requestedBy),
            reason     : trim(string: $data->reason),
            before     : $before,
            after      : $after,
            diff       : $this->diff(before: $before, after: $after),
            status     : TenantSecurityChangeRequestStatus::PENDING_APPROVAL,
            requestedAt: $this->clock->now()
        );

        $this->changeRequestStore->save(changeRequest: $changeRequest);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.requested',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id'    => $changeRequest->changeId,
                                                           'tenant'       => $changeRequest->tenantSlug,
                                                           'requested_by' => $changeRequest->requestedBy,
                                                           'diff'         => $this->encodeDiff(value: $changeRequest->diff),
                                                       ]
                                       ));

        return $changeRequest;
    }

    /**
     * @throws TenantSecurityFailed
     */
    private function assertKnownReferences(TenantSecurityConfiguration $after) : void
    {
        if (
            $after->federationConnectionId !== null
            && $this->federationConnectionStore?->find(connectionId: $after->federationConnectionId) === null
        ) {
            throw TenantSecurityFailed::unknownFederationConnection();
        }

        if (
            $after->scimDirectoryId !== null
            && $this->scimDirectoryStore?->find(directoryId: $after->scimDirectoryId) === null
        ) {
            throw TenantSecurityFailed::unknownScimDirectory();
        }
    }

    /**
     * @return array<string, string>
     */
    private function diff(TenantSecurityConfiguration|null $before, TenantSecurityConfiguration $after) : array
    {
        $beforeSnapshot = $this->snapshot(configuration: $before);
        $afterSnapshot  = $this->snapshot(configuration: $after);
        $diff           = [];

        foreach ($afterSnapshot as $field => $afterValue) {
            $beforeValue = $beforeSnapshot[$field] ?? '';

            if ($beforeValue === $afterValue) {
                continue;
            }

            $diff[$field] = $beforeValue . ' => ' . $afterValue;
        }

        ksort(array: $diff);

        return $diff;
    }

    /**
     * @return array<string, string>
     */
    private function snapshot(TenantSecurityConfiguration|null $configuration) : array
    {
        if ($configuration === null) {
            return [];
        }

        return [
            'federation_connection_id' => (string) $configuration->federationConnectionId,
            'scim_directory_id'        => (string) $configuration->scimDirectoryId,
            'verified_domains'         => implode(separator: ',', array: $configuration->verifiedDomains),
            'group_role_map'           => $this->encodeDiff(value: $configuration->groupRoleMap),
            'policy_profile'           => $configuration->policyProfile,
            'rollout_version'          => (string) $configuration->rolloutVersion,
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    private function encodeDiff(array $value) : string
    {
        try {
            return json_encode(value: $value, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '';
        }
    }
}
