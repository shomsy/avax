<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ReadClients;

use Avax\Auth\System\Capability\OAuth\OAuthClient;
use Avax\Auth\System\Capability\OAuth\OAuthClientRegistryInterface;

final readonly class ReadClients
{
    public function __construct(
        private OAuthClientRegistryInterface $clientRegistry
    ) {}

    /**
     * @return list<OAuthClient>
     */
    public function execute() : array
    {
        return $this->clientRegistry->all();
    }
}
