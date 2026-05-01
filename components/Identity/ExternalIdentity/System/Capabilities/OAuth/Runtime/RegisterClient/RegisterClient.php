<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\RegisteredOAuthClient;

final readonly class RegisterClient
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(RegisterClientData $data): RegisteredOAuthClient
    {
        $registered = $this->clientRegistry->register(
            name                           : $data->name,
            type                           : $data->type,
            redirectUris                   : $data->redirectUris,
            tenantSlug                     : $data->tenantSlug,
            allowedScopes                  : $data->allowedScopes,
            allowedAudiences               : $data->allowedAudiences,
            allowedGrantTypes              : $data->allowedGrantTypes,
            audienceScopeBoundaries        : $data->audienceScopeBoundaries,
            tokenEndpointAuthMethod        : $data->tokenEndpointAuthMethod,
            requiredSenderConstraint       : $data->requiredSenderConstraint,
            workloadIdentity               : $data->workloadIdentity,
            phishingResistantRequired      : $data->phishingResistantRequired,
            requestObjectSignatureRequired : $data->requestObjectSignatureRequired,
            frontChannelLogoutSupported    : $data->frontChannelLogoutSupported,
            backChannelLogoutSupported     : $data->backChannelLogoutSupported,
            approvalRequired               : $data->approvalRequired,
            requestObjectVerificationKeyPem: $data->requestObjectVerificationKeyPem,
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.registered',
            occurredAt: $this->clock->now(),
            context   : [
                            'client_id'         => $registered->client->clientId,
                            'tenant_slug'       => $registered->client->tenantSlug,
                            'name'              => $registered->client->name,
                            'type'              => $registered->client->type->value,
                            'workload_identity' => $registered->client->workloadIdentity ? 1 : 0,
                            'allowed_audiences' => implode(separator: ' ', array: $registered->client->allowedAudiences),
                            'sender_constraint' => $registered->client->requiredSenderConstraint?->value,
                'request_object_signature_required' => $registered->client->requestObjectSignatureRequired ? 1 : 0,
                            'approval_status'   => $registered->client->approvalStatus->value,
                            'approval_required' => $registered->client->isPendingApproval() ? 1 : 0,
            ],
        ));

        return $registered;
    }
}
