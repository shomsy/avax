<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\RegisterClient;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientRegistrarInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientDataValidatorInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\RegisteredClientData;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\CompositeClientDataValidator;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * High-level orchestrator for OIDC dynamic client registration.
 *
 * Banal: The OIDC Register Client file.
 */
final readonly class RegisterOidcClient
{
    public function __construct(
        private ClientRegistrarInterface $clientRegistrar,
        private ClientDataValidatorInterface $clientDataValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock
    ) {}

    /**
     * Registers a new OIDC client dynamically.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function execute(array $clientData) : RegisteredClientData
    {
        // Validate client data according to OIDC Dynamic Client Registration spec
        $validationResult = $this->clientDataValidator->validate($clientData);
        
        if (!$validationResult->isValid()) {
            throw new \InvalidArgumentException(
                'Invalid client data: ' . implode(', ', $validationResult->getErrors())
            );
        }

        // Register the client
        $registeredClient = $this->clientRegistrar->register($clientData);
        
        // Audit the registration
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oidc.client.registered',
            occurredAt: $this->clock->now(),
            context: [
                'client_id' => $registeredClient->getClientId(),
                'client_name' => $registeredClient->getClientName(),
                'redirect_uris' => $registeredClient->getRedirectUris(),
                'grant_types' => $registeredClient->getGrantTypes(),
                'token_endpoint_auth_method' => $registeredClient->getTokenEndpointAuthMethod(),
                'require_pkce' => $registeredClient->requirePkce(),
            ]
        ));

        return $registeredClient;
    }
}