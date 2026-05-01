<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ReadClients;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClient;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\OAuthClientRegistryInterface;

final readonly class ReadClients
{
    public function __construct(private OAuthClientRegistryInterface $clientRegistry) {}

    /**
     * @return list<OAuthClient>
     */
    public function execute(): array
    {
        return $this->clientRegistry->all();
    }
}
