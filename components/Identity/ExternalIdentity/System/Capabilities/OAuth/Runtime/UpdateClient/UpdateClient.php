<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\UpdateClient;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientApprovalStatus;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthTokenEndpointAuthMethodPolicy;
use RuntimeException;

final readonly class UpdateClient
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(UpdateClientData $data): OAuthClient
    {
        $existing = $this->clientRegistry->find(clientId: $data->clientId);

        if ($existing === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $tokenEndpointAuthMethod = new OAuthTokenEndpointAuthMethodPolicy()->resolve(
            type            : $data->type,
            requested       : $data->tokenEndpointAuthMethod,
            current         : $existing->tokenEndpointAuthMethod,
            workloadIdentity: $data->workloadIdentity,
        );
        $approvalRequired = $data->approvalRequired;
        $approvalStatus = $approvalRequired
            ? OAuthClientApprovalStatus::PENDING_APPROVAL
            : $existing->approvalStatus;
        $approvedAt = $approvalRequired ? null : $existing->approvedAt;
        $approvedBy = $approvalRequired ? null : $existing->approvedBy;

        $updated = new OAuthClient(
            clientId                       : $existing->clientId,
            name                           : trim(string: $data->name),
            type                           : $data->type,
            redirectUris                   : $data->redirectUris,
            allowedScopes                  : $data->allowedScopes,
            tenantSlug                     : $data->tenantSlug,
            allowedAudiences               : $data->allowedAudiences,
            allowedGrantTypes              : $data->allowedGrantTypes,
            audienceScopeBoundaries        : $data->audienceScopeBoundaries,
            tokenEndpointAuthMethod        : $tokenEndpointAuthMethod,
            requiredSenderConstraint       : $data->requiredSenderConstraint,
            workloadIdentity               : $data->workloadIdentity,
            phishingResistantRequired      : $data->phishingResistantRequired,
            requestObjectSignatureRequired : $data->requestObjectSignatureRequired,
            frontChannelLogoutSupported    : $data->frontChannelLogoutSupported,
            backChannelLogoutSupported     : $data->backChannelLogoutSupported,
            approvalStatus                 : $approvalStatus,
            approvedAt                     : $approvedAt,
            approvedBy                     : $approvedBy,
            active                         : $existing->active,
            secretHash                     : $existing->secretHash,
            requestObjectVerificationKeyPem: $data->requestObjectVerificationKeyPem,
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
                'request_object_signature_required' => $updated->requestObjectSignatureRequired ? 1 : 0,
                'approval_status' => $updated->approvalStatus->value,
            ],
        ));

        return $updated;
    }
}
