<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\DisableClient;

use Avax\Auth\System\Capabilities\OAuth\OAuthClient;
use Avax\Auth\System\Capabilities\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class DisableClient
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
                                                           'client_id'   => $client->clientId,
                                                           'tenant_slug' => $client->tenantSlug,
                                                       ]
                                       ));

        return $client;
    }
}
