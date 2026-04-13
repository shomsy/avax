<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\RegisterClient\UpdateClient;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientRegistrarInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\RegisteredClientData;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientStoreInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientDataValidatorInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrator for updating an OIDC client registration.
 *
 * Banal: The OIDC Client Update file.
 */
final readonly class UpdateOidcClient
{
    public function __construct(
        private ClientStoreInterface $clientStore,
        private ClientDataValidatorInterface $clientDataValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Updates an existing client registration.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(string $clientId, array $updates) : RegisteredClientData
    {
        // Find the existing client
        $existingClient = $this->clientStore->find($clientId);
        
        if ($existingClient === null) {
            throw new \InvalidArgumentException("Client not found: {$clientId}");
        }

        // Prepare updated data by merging existing with updates
        $updatedData = [
            'client_id' => $existingClient->getClientId(),
            'client_secret' => $existingClient->getClientSecret(),
            'client_name' => $updates['client_name'] ?? $existingClient->getClientName(),
            'redirect_uris' => $updates['redirect_uris'] ?? $existingClient->getRedirectUris(),
            'grant_types' => $updates['grant_types'] ?? $existingClient->getGrantTypes(),
            'token_endpoint_auth_method' => $updates['token_endpoint_auth_method'] ?? $existingClient->getTokenEndpointAuthMethod(),
            'require_pkce' => $updates['require_pkce'] ?? $existingClient->requirePkce(),
        ];

        // Validate the updated client data
        $validationResult = $this->clientDataValidator->validate($updatedData);
        
        if (!$validationResult->isValid()) {
            throw new \InvalidArgumentException(
                'Invalid client data: ' . implode(', ', $validationResult->getErrors())
            );
        }

        // Update the client
        $this->clientStore->update($clientId, $updatedData);

        // Get the updated client data
        $updatedClient = $this->clientStore->find($clientId);
        
        if ($updatedClient === null) {
            throw new \RuntimeException("Failed to retrieve updated client: {$clientId}");
        }

        // Audit the update
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oauth.client.updated',
            occurredAt: $this->clock->now(),
            context: [
                'client_id' => $updatedClient->getClientId(),
                'client_name' => $updatedClient->getClientName(),
                'redirect_uris' => $updatedClient->getRedirectUris(),
                'grant_types' => $updatedClient->getGrantTypes(),
                'token_endpoint_auth_method' => $updatedClient->getTokenEndpointAuthMethod(),
                'require_pkce' => $updatedClient->requirePkce(),
            ]
        ));

        return $updatedClient;
    }
}