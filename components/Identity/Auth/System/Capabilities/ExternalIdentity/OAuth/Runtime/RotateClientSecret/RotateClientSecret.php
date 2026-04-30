<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RotateClientSecret;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\RegisteredOAuthClient;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use RuntimeException;

final readonly class RotateClientSecret
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry, private AuditLogInterface $auditLog, private Clock $clock) {}

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
                                                       ],
                                       ));

        return $registered;
    }
}
