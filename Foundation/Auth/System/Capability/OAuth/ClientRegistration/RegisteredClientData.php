<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

/**
 * Data transfer object for registered client information.
 */
final readonly class RegisteredClientData
{
    public function __construct(
        private string $clientId,
        private string|null $clientSecret = null,
        private string|null $clientName = null,
        /** @var array<string> */ private array $redirectUris = [],
        /** @var array<string> */ private array $grantTypes = [],
        private string $tokenEndpointAuthMethod = 'client_secret_basic',
        private bool $requirePkce = false,
        private \DateTimeImmutable $registeredAt
    ) {}

    public function getClientId() : string
    {
        return $this->clientId;
    }

    public function getClientSecret() : ?string
    {
        return $this->clientSecret;
    }

    public function getClientName() : ?string
    {
        return $this->clientName;
    }

    /**
     * @return array<string>
     */
    public function getRedirectUris() : array
    {
        return $this->redirectUris;
    }

    /**
     * @return array<string>
     */
    public function getGrantTypes() : array
    {
        return $this->grantTypes;
    }

    public function getTokenEndpointAuthMethod() : string
    {
        return $this->tokenEndpointAuthMethod;
    }

    public function requirePkce() : bool
    {
        return $this->requirePkce;
    }

    public function getRegisteredAt() : \DateTimeImmutable
    {
        return $this->registeredAt;
    }
}