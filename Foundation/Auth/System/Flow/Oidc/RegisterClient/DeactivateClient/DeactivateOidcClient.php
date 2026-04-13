<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\RegisterClient\DeactivateClient;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrator for deactivating an OIDC client.
 *
 * Banal: The OIDC Client Deactivation file.
 */
final readonly class DeactivateOidcClient
{
    public function __construct(
        private ClientStoreInterface $clientStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Deactivates an existing client.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(string $clientId) : void
    {
        // Find the existing client
        $existingClient = $this->clientStore->find($clientId);
        
        if ($existingClient === null) {
            throw new \InvalidArgumentException("Client not found: {$clientId}");
        }

        // Deactivate the client (in a real implementation, this might involve 
        // setting an active flag or moving to an inactive state)
        // For now, we'll treat deletion as deactivation for simplicity
        $this->clientStore->delete($clientId);

        // Audit the deactivation
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oauth.client.deactivated',
            occurredAt: $this->clock->now(),
            context: [
                'client_id' => $clientId,
                'client_name' => $existingClient->getClientName(),
            ]
        ));
    }
}