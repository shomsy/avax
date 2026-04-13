<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Support;

use Avax\Auth\System\Capability\Federation\FederatedIdentity;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationConnectionHealth;
use Avax\Auth\System\Capability\Federation\FederationHealthCheckInterface;
use Avax\Auth\System\Capability\Federation\FederationMetadata;
use Avax\Auth\System\Capability\Federation\FederationMetadataRuntimeInterface;
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;

final class FakeFederationRuntime implements FederationRuntimeInterface, FederationMetadataRuntimeInterface, FederationHealthCheckInterface
{
    public FederationConnectionHealth $health = FederationConnectionHealth::HEALTHY;

    public function startLogin(
        FederationConnection $connection,
        string $redirectUri,
        string|null $state = null
    ) : StartedFederatedLogin
    {
        return new StartedFederatedLogin(
            redirectUrl: 'https://idp.example.test/login?connection=' . $connection->connectionId . '&redirect=' . urlencode($redirectUri),
            state      : $state
        );
    }

    public function completeLogin(
        FederationConnection $connection,
        array $payload
    ) : FederatedIdentity
    {
        return new FederatedIdentity(
            subject      : (string) ($payload['subject'] ?? 'subject-1'),
            email        : (string) ($payload['email'] ?? 'federated@example.com'),
            displayName  : (string) ($payload['display_name'] ?? 'Federated User'),
            groups       : array_values(array_map('strval', $payload['groups'] ?? [])),
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
