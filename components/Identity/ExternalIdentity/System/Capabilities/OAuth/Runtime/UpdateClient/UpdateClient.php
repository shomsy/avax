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
    public function __construct(private OAuthClientRegistryInterface $oAuthClientRegistry, private AuditLogInterface $auditLog, private Clock $clock)
    {
    }

    public function execute(UpdateClientData $updateClientData): OAuthClient
    {
        $existing = $this->oAuthClientRegistry->find(clientId: $updateClientData->clientId);

        if (! $existing instanceof OAuthClient) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $oAuthTokenEndpointAuthMethod = new OAuthTokenEndpointAuthMethodPolicy()->resolve(
            requested       : $updateClientData->tokenEndpointAuthMethod,
            current         : $existing->tokenEndpointAuthMethod,
            workloadIdentity: $updateClientData->workloadIdentity,
            type            : $updateClientData->type,
        );
        $approvalRequired = $updateClientData->approvalRequired;
        $approvalStatus = $approvalRequired
            ? OAuthClientApprovalStatus::PENDING_APPROVAL
            : $existing->approvalStatus;
        $approvedAt = $approvalRequired ? null : $existing->approvedAt;
        $approvedBy = $approvalRequired ? null : $existing->approvedBy;

        $oAuthClient = new OAuthClient(
            clientId                       : $existing->clientId,
            name                           : trim(string: $updateClientData->name),
            type                           : $updateClientData->type,
            redirectUris                   : $updateClientData->redirectUris,
            allowedScopes                  : $updateClientData->allowedScopes,
            tenantSlug                     : $updateClientData->tenantSlug,
            allowedAudiences               : $updateClientData->allowedAudiences,
            allowedGrantTypes              : $updateClientData->allowedGrantTypes,
            audienceScopeBoundaries        : $updateClientData->audienceScopeBoundaries,
            requiredSenderConstraint       : $updateClientData->requiredSenderConstraint,
            workloadIdentity               : $updateClientData->workloadIdentity,
            phishingResistantRequired      : $updateClientData->phishingResistantRequired,
            requestObjectSignatureRequired : $updateClientData->requestObjectSignatureRequired,
            frontChannelLogoutSupported    : $updateClientData->frontChannelLogoutSupported,
            backChannelLogoutSupported     : $updateClientData->backChannelLogoutSupported,
            approvedAt                     : $approvedAt,
            approvedBy                     : $approvedBy,
            active                         : $existing->active,
            secretHash                     : $existing->secretHash,
            requestObjectVerificationKeyPem: $updateClientData->requestObjectVerificationKeyPem,
            tokenEndpointAuthMethod        : $oAuthTokenEndpointAuthMethod,
            approvalStatus                 : $approvalStatus,
        );
        $this->oAuthClientRegistry->replace(client: $oAuthClient);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.updated',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $oAuthClient->clientId,
                'tenant_slug' => $oAuthClient->tenantSlug,
                'type' => $oAuthClient->type->value,
                'active' => $oAuthClient->active ? 1 : 0,
                'request_object_signature_required' => $oAuthClient->requestObjectSignatureRequired ? 1 : 0,
                'approval_status' => $oAuthClient->approvalStatus->value,
            ],
        ));

        return $oAuthClient;
    }
}
