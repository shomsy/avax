<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Support;

use Avax\Auth\System\Capability\Federation\FederatedIdentity;
use Avax\Auth\System\Capability\Federation\FederationConnection;
use Avax\Auth\System\Capability\Federation\FederationRuntimeInterface;
use Avax\Auth\System\Capability\Federation\StartedFederatedLogin;

final class FakeFederationRuntime implements FederationRuntimeInterface
{
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
}
