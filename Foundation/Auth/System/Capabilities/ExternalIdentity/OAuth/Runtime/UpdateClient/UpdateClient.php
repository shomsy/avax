<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\UpdateClient;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientApprovalStatus;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthTokenEndpointAuthMethodPolicy;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class UpdateClient
{
    private Clock                        $clock;
    private AuditLogInterface            $auditLog;
    private OAuthClientRegistryInterface $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface $clientRegistry,
        AuditLogInterface            $auditLog,
        Clock                        $clock
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->auditLog       = $auditLog;
        $this->clock          = $clock;
    }

    public function execute(UpdateClientData $data) : OAuthClient
    {
        $existing = $this->clientRegistry->find(clientId: $data->clientId);

        if ($existing === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $tokenEndpointAuthMethod = (new OAuthTokenEndpointAuthMethodPolicy())->resolve(
            type            : $data->type,
            requested       : $data->tokenEndpointAuthMethod,
            current         : $existing->tokenEndpointAuthMethod,
            workloadIdentity: $data->workloadIdentity
        );
        $approvalRequired        = $data->approvalRequired;
        $approvalStatus          = $approvalRequired
            ? OAuthClientApprovalStatus::PENDING_APPROVAL
            : $existing->approvalStatus;
        $approvedAt              = $approvalRequired ? null : $existing->approvedAt;
        $approvedBy              = $approvalRequired ? null : $existing->approvedBy;

        $updated = new OAuthClient(
            clientId                       : $existing->clientId,
            name                           : trim($data->name),
            type                           : $data->type,
            redirectUris                   : array_values($data->redirectUris),
            allowedScopes                  : array_values($data->allowedScopes),
            tenantSlug                     : $data->tenantSlug,
            allowedAudiences               : array_values($data->allowedAudiences),
            allowedGrantTypes              : array_values($data->allowedGrantTypes),
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
            requestObjectVerificationKeyPem: $data->requestObjectVerificationKeyPem
        );
        $this->clientRegistry->replace(client: $updated);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.client.updated',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'                         => $updated->clientId,
                                                           'tenant_slug'                       => $updated->tenantSlug,
                                                           'type'                              => $updated->type->value,
                                                           'active'                            => $updated->active ? 1 : 0,
                                                           'request_object_signature_required' => $updated->requestObjectSignatureRequired ? 1 : 0,
                                                           'approval_status'                   => $updated->approvalStatus->value,
                                                       ]
                                       ));

        return $updated;
    }
}
