<?php

declare(strict_types=1);

namespace components\Auth\Tests\Support;

use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederatedIdentity;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnection;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationConnectionHealth;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationHealthCheckInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationMetadata;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationMetadataRuntimeInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationRuntimeInterface;
use components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\StartedFederatedLogin;

final class FakeFederationRuntime implements FederationRuntimeInterface, FederationMetadataRuntimeInterface, FederationHealthCheckInterface
{
    public FederationConnectionHealth $health = FederationConnectionHealth::HEALTHY;

    public function startLogin(
        FederationConnection $connection,
        string               $redirectUri,
        string|null          $state = null
    ) : StartedFederatedLogin
    {
        return new StartedFederatedLogin(
            redirectUrl: 'https://idp.example.test/login?connection=' . $connection->connectionId . '&redirect=' . urlencode(string: $redirectUri),
            state      : $state
        );
    }

    public function completeLogin(
        FederationConnection $connection,
        array                $payload
    ) : FederatedIdentity
    {
        return new FederatedIdentity(
            subject      : (string) ($payload['subject'] ?? 'subject-1'),
            email        : (string) ($payload['email'] ?? 'federated@example.com'),
            displayName  : (string) ($payload['display_name'] ?? 'Federated User'),
            groups       : array_values(array: array_map(callback: 'strval', array: $payload['groups'] ?? [])),
            emailVerified: (bool) ($payload['email_verified'] ?? true)
        );
    }

    public function readMetadata(FederationConnection $connection) : FederationMetadata
    {
        return new FederationMetadata(
            issuer         : 'https://idp.example.test/' . $connection->connectionId,
            singleSignOnUrl: 'https://idp.example.test/sso/' . $connection->connectionId,
            claims         : ['provider' => $connection->provider->value]
        );
    }

    public function checkHealth(FederationConnection $connection) : FederationConnectionHealth
    {
        return $this->health;
    }
}
