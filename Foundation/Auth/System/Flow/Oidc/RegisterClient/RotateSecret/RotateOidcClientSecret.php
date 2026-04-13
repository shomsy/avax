<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\RegisterClient\RotateSecret;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientRegistrarInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\RegisteredClientData;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Orchestrator for rotating an OIDC client secret.
 *
 * Banal: The OIDC Client Secret Rotation file.
 */
final readonly class RotateOidcClientSecret
{
    public function __construct(
        private ClientStoreInterface $clientStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Rotates the secret for an existing client.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(string $clientId) : string
    {
        // Find the existing client
        $existingClient = $this->clientStore->find($clientId);
        
        if ($existingClient === null) {
            throw new \InvalidArgumentException("Client not found: {$clientId}");
        }

        // Generate new secret
        $newSecret = bin2hex(random_bytes(32));

        // Update the client with new secret
        $this->clientStore->update($clientId, [
            'clientSecret' => $newSecret
        ]);

        // Audit the secret rotation
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oauth.client.secret.rotated',
            occurredAt: $this->clock->now(),
            context: [
                'client_id' => $clientId,
                'client_name' => $existingClient->getClientName(),
            ]
        ));

        return $newSecret;
    }
}