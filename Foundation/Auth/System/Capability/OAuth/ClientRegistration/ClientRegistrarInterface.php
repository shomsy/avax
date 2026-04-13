<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

use Avax\Auth\System\Capability\OAuth\ClientRegistration\RegisteredClientData;

/**
 * Interface for registering OAuth/OIDC clients.
 */
interface ClientRegistrarInterface
{
    /**
     * Registers a new client.
     *
     * @param array $clientData Client registration data
     * @return RegisteredClientData Registered client information
     */
    public function register(array $clientData) : RegisteredClientData;
}