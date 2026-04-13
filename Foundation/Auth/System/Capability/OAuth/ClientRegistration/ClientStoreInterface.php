<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth\ClientRegistration;

/**
 * Interface for OAuth/OIDC client storage operations.
 */
interface ClientStoreInterface
{
    public function find(string $clientId) : ?RegisteredClientData;
    public function save(RegisteredClientData $client) : void;
    public function update(string $clientId, array $updates) : void;
    public function delete(string $clientId) : void;
}