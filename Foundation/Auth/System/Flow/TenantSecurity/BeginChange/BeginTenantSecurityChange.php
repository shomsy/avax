<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\TenantSecurity\BeginChange;

use Avax\Auth\System\Capability\Federation\FederationConnectionStoreInterface;
use Avax\Auth\System\Capability\Scim\ScimDirectoryStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequest;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStatus;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityChangeRequestStoreInterface;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfiguration;
use Avax\Auth\System\Capability\TenantSecurity\TenantSecurityConfigurationStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\TenantSecurity\TenantSecurityFailed;
use Avax\Auth\System\Foundation\Clock;
use JsonException;

final readonly class BeginTenantSecurityChange
{
    public function __construct(
        private TenantSecurityConfigurationStoreInterface $configurationStore,
        private TenantSecurityChangeRequestStoreInterface $changeRequestStore,
        private FederationConnectionStoreInterface|null $federationConnectionStore,
        private ScimDirectoryStoreInterface|null $scimDirectoryStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * @throws TenantSecurityFailed
     */
    public function execute(BeginTenantSecurityChangeData $data) : TenantSecurityChangeRequest
    {
        $this->assertKnownReferences(after: $data->after);

        $before = $this->configurationStore->find(tenantSlug: $data->tenantSlug);
        $after = new TenantSecurityConfiguration(
            tenantSlug             : $data->tenantSlug,
            federationConnectionId : $data->after->federationConnectionId,
            scimDirectoryId        : $data->after->scimDirectoryId,
            verifiedDomains        : $data->after->verifiedDomains,
            groupRoleMap           : $data->after->groupRoleMap,
            policyProfile          : $data->after->policyProfile,
            rolloutVersion         : $before !== null ? $before->rolloutVersion + 1 : $data->after->rolloutVersion
        );
        $changeRequest = new TenantSecurityChangeRequest(
            changeId     : 'tenant_change_' . bin2hex(random_bytes(12)),
            tenantSlug   : $data->tenantSlug,
            requestedBy  : trim($data->requestedBy),
            reason       : trim($data->reason),
            before       : $before,
            after        : $after,
            diff         : $this->diff(before: $before, after: $after),
            status       : TenantSecurityChangeRequestStatus::PENDING_APPROVAL,
            requestedAt  : $this->clock->now()
        );

        $this->changeRequestStore->save(changeRequest: $changeRequest);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.tenant_security.change.requested',
            occurredAt: $this->clock->now(),
            context   : [
                            'change_id' => $changeRequest->changeId,
                            'tenant' => $changeRequest->tenantSlug,
                            'requested_by' => $changeRequest->requestedBy,
                            'diff' => $this->encodeDiff(value: $changeRequest->diff),
            ]
        ));

        return $changeRequest;
    }

    /**
     * @return array<string, string>
     */
    private function diff(TenantSecurityConfiguration|null $before, TenantSecurityConfiguration $after) : array
    {
        $beforeSnapshot = $this->snapshot(configuration: $before);
        $afterSnapshot = $this->snapshot(configuration: $after);
        $diff = [];

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
            'scim_directory_id' => (string) $configuration->scimDirectoryId,
            'verified_domains' => implode(',', $configuration->verifiedDomains),
            'group_role_map' => $this->encodeDiff(value: $configuration->groupRoleMap),
            'policy_profile' => $configuration->policyProfile,
            'rollout_version' => (string) $configuration->rolloutVersion,
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
}
