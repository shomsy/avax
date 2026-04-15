<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\RotateClientSecret;

use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;
use Avax\Auth\System\Capability\OAuth\RegisteredOAuthClient;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class RotateClientSecret
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

    public function execute(string $clientId) : RegisteredOAuthClient
    {
        $registered = $this->clientRegistry->rotateSecret(clientId: $clientId);

        if ($registered === null) {
            throw new RuntimeException(message: 'OAuth client was not found.');
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.oauth.client.secret_rotated',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'client_id'     => $registered->client->clientId,
                                                           'tenant_slug'   => $registered->client->tenantSlug,
                                                           'public_client' => $registered->client->isPublic() ? 1 : 0,
                                                       ]
                                       ));

        return $registered;
    }
}
