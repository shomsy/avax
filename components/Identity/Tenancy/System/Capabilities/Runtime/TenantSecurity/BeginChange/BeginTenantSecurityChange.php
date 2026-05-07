<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\BeginChange;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectory;
use Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Directories\ScimDirectoryStoreInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnection;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\Federation\FederationConnectionStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequest;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStatus;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfiguration;
use Avax\Components\Identity\Tenancy\System\Capabilities\Security\TenantSecurityConfigurationStoreInterface;
use JsonException;
use Random\RandomException;

final readonly class BeginTenantSecurityChange
{
    public function __construct(private TenantSecurityConfigurationStoreInterface $tenantSecurityConfigurationStore, private TenantSecurityChangeRequestStoreInterface $tenantSecurityChangeRequestStore, private ?FederationConnectionStoreInterface $federationConnectionStore, private ?ScimDirectoryStoreInterface $scimDirectoryStore, private AuditLogInterface $auditLog, private Clock $clock) {}

    /**
     * @throws TenantSecurityFailed
     * @throws RandomException
     */
    public function execute(BeginTenantSecurityChangeData $beginTenantSecurityChangeData) : TenantSecurityChangeRequest
    {
        $this->assertKnownReferences(after: $beginTenantSecurityChangeData->after);

        $before                      = $this->tenantSecurityConfigurationStore->find(tenantSlug: $beginTenantSecurityChangeData->tenantSlug);
        $tenantSecurityConfiguration = new TenantSecurityConfiguration(
            tenantSlug            : $beginTenantSecurityChangeData->tenantSlug,
            federationConnectionId: $beginTenantSecurityChangeData->after->federationConnectionId,
            scimDirectoryId       : $beginTenantSecurityChangeData->after->scimDirectoryId,
            verifiedDomains       : $beginTenantSecurityChangeData->after->verifiedDomains,
            groupRoleMap          : $beginTenantSecurityChangeData->after->groupRoleMap,
            policyProfile         : $beginTenantSecurityChangeData->after->policyProfile,
            rolloutVersion        : $before instanceof TenantSecurityConfiguration ? $before->rolloutVersion + 1 : $beginTenantSecurityChangeData->after->rolloutVersion,
        );
        $tenantSecurityChangeRequest = new TenantSecurityChangeRequest(
            changeId   : 'tenant_change_' . bin2hex(string: random_bytes(length: 12)),
            tenantSlug : $beginTenantSecurityChangeData->tenantSlug,
            requestedBy: trim(string: $beginTenantSecurityChangeData->requestedBy),
            reason     : trim(string: $beginTenantSecurityChangeData->reason),
            before     : $before,
            after      : $tenantSecurityConfiguration,
            diff       : $this->diff(before: $before, after: $tenantSecurityConfiguration),
            status     : TenantSecurityChangeRequestStatus::PENDING_APPROVAL,
            requestedAt: $this->clock->now(),
        );

        $this->tenantSecurityChangeRequestStore->save(changeRequest: $tenantSecurityChangeRequest);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.tenant_security.change.requested',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'change_id'    => $tenantSecurityChangeRequest->changeId,
                                                           'tenant'       => $tenantSecurityChangeRequest->tenantSlug,
                                                           'requested_by' => $tenantSecurityChangeRequest->requestedBy,
                                                           'diff'         => $this->encodeDiff(value: $tenantSecurityChangeRequest->diff),
                                                       ],
                                       ));

        return $tenantSecurityChangeRequest;
    }

    /**
     * @throws TenantSecurityFailed
     */
    private function assertKnownReferences(TenantSecurityConfiguration $tenantSecurityConfiguration) : void
    {
        if (
            $tenantSecurityConfiguration->federationConnectionId !== null
            && ! $this->federationConnectionStore?->find(connectionId: $tenantSecurityConfiguration->federationConnectionId) instanceof FederationConnection
        ) {
            throw TenantSecurityFailed::unknownFederationConnection();
        }

        if (
            $tenantSecurityConfiguration->scimDirectoryId !== null
            && ! $this->scimDirectoryStore?->find(directoryId: $tenantSecurityConfiguration->scimDirectoryId) instanceof ScimDirectory
        ) {
            throw TenantSecurityFailed::unknownScimDirectory();
        }
    }

    /**
     * @return array<string, string>
     */
    private function diff(?TenantSecurityConfiguration $before, TenantSecurityConfiguration $after) : array
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
    private function snapshot(?TenantSecurityConfiguration $tenantSecurityConfiguration) : array
    {
        if (! $tenantSecurityConfiguration instanceof TenantSecurityConfiguration) {
            return [];
        }

        return [
            'federation_connection_id' => (string) $tenantSecurityConfiguration->federationConnectionId,
            'scim_directory_id'        => (string) $tenantSecurityConfiguration->scimDirectoryId,
            'verified_domains'         => implode(separator: ',', array: $tenantSecurityConfiguration->verifiedDomains),
            'group_role_map'           => $this->encodeDiff(value: $tenantSecurityConfiguration->groupRoleMap),
            'policy_profile'           => $tenantSecurityConfiguration->policyProfile,
            'rollout_version'          => (string) $tenantSecurityConfiguration->rolloutVersion,
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
