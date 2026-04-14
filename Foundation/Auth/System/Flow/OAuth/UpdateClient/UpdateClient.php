<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\UpdateClient;

use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientApprovalStatus;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\OAuthTokenEndpointAuthMethodPolicy;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class UpdateClient
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(UpdateClientData $data) : OAuthClient
    {
        $existing = $this->clientRegistry->find(clientId: $data->clientId);

        if ($existing === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $tokenEndpointAuthMethod = (new OAuthTokenEndpointAuthMethodPolicy())->resolve(
            type: $data->type,
            requested: $data->tokenEndpointAuthMethod,
            current: $existing->tokenEndpointAuthMethod,
            workloadIdentity: $data->workloadIdentity
        );
        $approvalRequired = $data->approvalRequired;
        $approvalStatus = $approvalRequired
            ? OAuthClientApprovalStatus::PENDING_APPROVAL
            : $existing->approvalStatus;
        $approvedAt = $approvalRequired ? null : $existing->approvedAt;
        $approvedBy = $approvalRequired ? null : $existing->approvedBy;

        $updated = new OAuthClient(
            clientId                  : $existing->clientId,
            name                      : trim($data->name),
            type                      : $data->type,
            redirectUris              : array_values($data->redirectUris),
            allowedScopes             : array_values($data->allowedScopes),
            tenantSlug                : $data->tenantSlug,
            allowedAudiences          : array_values($data->allowedAudiences),
            allowedGrantTypes         : array_values($data->allowedGrantTypes),
            audienceScopeBoundaries   : $data->audienceScopeBoundaries,
            tokenEndpointAuthMethod   : $tokenEndpointAuthMethod,
            requiredSenderConstraint  : $data->requiredSenderConstraint,
            workloadIdentity          : $data->workloadIdentity,
            phishingResistantRequired : $data->phishingResistantRequired,
            frontChannelLogoutSupported: $data->frontChannelLogoutSupported,
            backChannelLogoutSupported : $data->backChannelLogoutSupported,
            approvalStatus            : $approvalStatus,
            approvedAt                : $approvedAt,
            approvedBy                : $approvedBy,
            active                    : $existing->active,
            secretHash                : $existing->secretHash
        );
        $this->clientRegistry->replace(client: $updated);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.updated',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $updated->clientId,
                'tenant_slug' => $updated->tenantSlug,
                'type' => $updated->type->value,
                'active' => $updated->active ? 1 : 0,
                'approval_status' => $updated->approvalStatus->value,
            ]
        ));

        return $updated;
    }
}
