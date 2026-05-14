<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RegisterClient;

use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\AuthDiagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\RegisteredOAuthClient;

final readonly class RegisterClient
{
    public function __construct(private OAuthClientRegistryInterface $oAuthClientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(RegisterClientData $registerClientData) : RegisteredOAuthClient
    {
        $registeredOAuthClient = $this->oAuthClientRegistry->register(
            name                           : $registerClientData->name,
            redirectUris                   : $registerClientData->redirectUris,
            tenantSlug                     : $registerClientData->tenantSlug,
            allowedScopes                  : $registerClientData->allowedScopes,
            allowedAudiences               : $registerClientData->allowedAudiences,
            allowedGrantTypes              : $registerClientData->allowedGrantTypes,
            audienceScopeBoundaries        : $registerClientData->audienceScopeBoundaries,
            workloadIdentity               : $registerClientData->workloadIdentity,
            phishingResistantRequired      : $registerClientData->phishingResistantRequired,
            requestObjectSignatureRequired : $registerClientData->requestObjectSignatureRequired,
            frontChannelLogoutSupported    : $registerClientData->frontChannelLogoutSupported,
            backChannelLogoutSupported     : $registerClientData->backChannelLogoutSupported,
            approvalRequired               : $registerClientData->approvalRequired,
            requestObjectVerificationKeyPem: $registerClientData->requestObjectVerificationKeyPem,
            type                           : $registerClientData->type,
            tokenEndpointAuthMethod        : $registerClientData->tokenEndpointAuthMethod,
            requiredSenderConstraint       : $registerClientData->requiredSenderConstraint,
        );

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.client.registered',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'                         => $registeredOAuthClient->client->clientId,
                                                           'tenant_slug'                       => $registeredOAuthClient->client->tenantSlug,
                                                           'name'                              => $registeredOAuthClient->client->name,
                                                           'type'                              => $registeredOAuthClient->client->type->value,
                                                           'workload_identity'                 => $registeredOAuthClient->client->workloadIdentity ? 1 : 0,
                                                           'allowed_audiences'                 => implode(separator: ' ', array: $registeredOAuthClient->client->allowedAudiences),
                                                           'sender_constraint'                 => $registeredOAuthClient->client->requiredSenderConstraint?->value,
                                                           'request_object_signature_required' => $registeredOAuthClient->client->requestObjectSignatureRequired ? 1 : 0,
                                                           'approval_status'                   => $registeredOAuthClient->client->approvalStatus->value,
                                                           'approval_required'                 => $registeredOAuthClient->client->isPendingApproval() ? 1 : 0,
                                                       ],
                                       ));

        return $registeredOAuthClient;
    }
}
