<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\BeginChange;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionStoreInterface;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Support\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Runtime\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequest;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfiguration;
use Avax\Auth\System\Capabilities\Tenancy\Security\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use JsonException;
use Random\RandomException;

final readonly class BeginTenantSecurityChange
{
    private Clock                                     $clock;
    private AuditLogInterface                         $auditLog;
    private ScimDirectoryStoreInterface|null          $scimDirectoryStore;
    private FederationConnectionStoreInterface|null   $federationConnectionStore;
    private TenantSecurityChangeRequestStoreInterface $changeRequestStore;
    private TenantSecurityConfigurationStoreInterface $configurationStore;

    public function __construct(
        TenantSecurityConfigurationStoreInterface $configurationStore,
        TenantSecurityChangeRequestStoreInterface $changeRequestStore,
        FederationConnectionStoreInterface|null   $federationConnectionStore,
        ScimDirectoryStoreInterface|null          $scimDirectoryStore,
        AuditLogInterface                         $auditLog,
        Clock                                     $clock
    )
    {
        $this->configurationStore        = $configurationStore;
        $this->changeRequestStore        = $changeRequestStore;
        $this->federationConnectionStore = $federationConnectionStore;
        $this->scimDirectoryStore        = $scimDirectoryStore;
        $this->auditLog                  = $auditLog;
        $this->clock                     = $clock;
    }

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
            changeId   : 'tenant_change_' . bin2hex(random_bytes(12)),
            tenantSlug : $data->tenantSlug,
            requestedBy: trim($data->requestedBy),
            reason     : trim($data->reason),
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

        ksort($diff);

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
            'verified_domains'         => implode(',', $configuration->verifiedDomains),
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
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return '';
        }
    }
}
