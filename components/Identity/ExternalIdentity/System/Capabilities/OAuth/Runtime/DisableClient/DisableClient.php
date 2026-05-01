<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\DisableClient;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;
use RuntimeException;

final readonly class DisableClient
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

    public function execute(string $clientId): OAuthClient
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
            ],
        ));

        return $client;
    }
}
