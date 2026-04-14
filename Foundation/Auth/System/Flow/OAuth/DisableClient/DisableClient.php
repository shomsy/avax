<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\DisableClient;

use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class DisableClient
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    public function execute(string $clientId) : OAuthClient
    {
        $client = $this->clientRegistry->deactivate(clientId: $clientId);

        if ($client === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.oauth.client.disabled',
            occurredAt: $this->clock->now(),
            context   : [
                'client_id' => $client->clientId,
                'tenant_slug' => $client->tenantSlug,
            ]
        ));

        return $client;
    }
}
