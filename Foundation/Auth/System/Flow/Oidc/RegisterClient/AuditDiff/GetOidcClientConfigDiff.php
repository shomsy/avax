<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\RegisterClient\AuditDiff;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Orchestrator for getting configuration diff of an OIDC client.
 *
 * Banal: The OIDC Client Config Diff file.
 */
final readonly class GetOidcClientConfigDiff
{
    public function __construct(
        private ClientStoreInterface $clientStore,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Gets the configuration diff for a client between two points in time.
     * In a real implementation, this would compare current config with historical versions.
     * For this implementation, we'll return the current configuration as the "diff" from empty state.
     *
     * @throws \InvalidArgumentException
     */
    public function execute(string $clientId) : array
    {
        // Find the existing client
        $existingClient = $this->clientStore->find($clientId);
        
        if ($existingClient === null) {
            throw new \InvalidArgumentException("Client not found: {$clientId}");
        }

        // Create diff representation (current state vs empty/initial state)
        $diff = [
            'client_id' => $existingClient->getClientId(),
            'client_name' => $existingClient->getClientName(),
            'client_secret' => '***REDACTED***', // Never expose secret in diffs/audits
            'redirect_uris' => $existingClient->getRedirectUris(),
            'grant_types' => $existingClient->getGrantTypes(),
            'token_endpoint_auth_method' => $existingClient->getTokenEndpointAuthMethod(),
            'require_pkce' => $existingClient->requirePkce(),
            'registered_at' => $existingClient->getRegisteredAt()->format('c'),
        ];

        // Audit the diff retrieval
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oauth.client.config.diff.retrieved',
            occurredAt: $this->clock->now(),
            context: [
                'client_id' => $existingClient->getClientId(),
                'client_name' => $existingClient->getClientName(),
            ]
        ));

        return $diff;
    }
}