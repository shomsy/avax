<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ReadClients;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClient;
use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\OAuthClientRegistryInterface;

final readonly class ReadClients
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry) {}

    /**
     * @return list<OAuthClient>
     */
    public function execute() : array
    {
        return $this->clientRegistry->all();
    }
}
