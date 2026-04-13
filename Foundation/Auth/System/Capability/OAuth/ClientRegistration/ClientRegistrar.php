<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientDataValidatorInterface;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\RegisteredClientData;
use Avax\Auth\System\Capability\OAuth\ClientRegistration\ClientStoreInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\System\Foundation\IdGeneratorInterface;
use SensitiveParameter;

/**
 * Default implementation of OAuth/OIDC client registrar.
 *
 * Banal: The OAuth Client Registrar file.
 */
final readonly class ClientRegistrar implements ClientRegistrarInterface
{
    public function __construct(
        private ClientStoreInterface $clientStore,
        private ClientDataValidatorInterface $clientDataValidator,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private IdGeneratorInterface $idGenerator
    ) {}

    /**
     * Registers a new client.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function register(array $clientData) : RegisteredClientData
    {
        // Validate client data
        $validationResult = $this->clientDataValidator->validate($clientData);
        
        if (!$validationResult->isValid()) {
            throw new \InvalidArgumentException(
                'Invalid client data: ' . implode(', ', $validationResult->getErrors())
            );
        }

        // Generate client ID and secret
        $clientId = (string) $this->idGenerator->generate();
        $clientSecret = bin2hex(random_bytes(32));

        // Create registered client data
        $registeredClient = new RegisteredClientData(
            clientId: $clientId,
            clientSecret: $clientSecret,
            clientName: $clientData['client_name'] ?? null,
            redirectUris: $clientData['redirect_uris'] ?? [],
            grantTypes: $clientData['grant_types'] ?? [],
            tokenEndpointAuthMethod: $clientData['token_endpoint_auth_method'] ?? 'client_secret_basic',
            requirePkce: $clientData['require_pkce'] ?? false,
            registeredAt: $this->clock->now()
        );

        // Save the client
        $this->clientStore->save($registeredClient);

        // Audit the registration
        $this->auditLog->record(new AuditEvent(
            name: 'auth.oauth.client.registered',
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