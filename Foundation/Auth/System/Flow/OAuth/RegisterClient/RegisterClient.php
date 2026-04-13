<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\RegisterClient;

use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

final readonly class RegisterClient
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        private AuditLogInterface            $auditLog,
        private Clock                        $clock
    ) {}

    public function execute(RegisterClientData $data) : RegisteredOAuthClient
    {
        $registered = $this->clientRegistry->register(
            name                      : $data->name,
            type                      : $data->type,
            redirectUris              : $data->redirectUris,
            allowedScopes             : $data->allowedScopes,
            allowedAudiences          : $data->allowedAudiences,
            allowedGrantTypes         : $data->allowedGrantTypes,
            audienceScopeBoundaries   : $data->audienceScopeBoundaries,
            requiredSenderConstraint  : $data->requiredSenderConstraint,
            workloadIdentity          : $data->workloadIdentity,
            phishingResistantRequired : $data->phishingResistantRequired
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.registered',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $registered->client->clientId,
                'name' => $registered->client->name,
                'type' => $registered->client->type->value,
                'workload_identity' => $registered->client->workloadIdentity ? 1 : 0,
                'allowed_audiences' => implode(' ', $registered->client->allowedAudiences),
                'sender_constraint' => $registered->client->requiredSenderConstraint?->value,
            ]
        ));

        return $registered;
    }
}
