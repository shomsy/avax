<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ReadClients;

use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;

final readonly class ReadClients
{
    private OAuthClientRegistryInterface $clientRegistry;

    public function __construct(
        OAuthClientRegistryInterface $clientRegistry
    )
    {
        $this->clientRegistry = $clientRegistry;
    }

    /**
     * @return list<OAuthClient>
     */
    public function execute() : array
    {
        return $this->clientRegistry->all();
    }
}
