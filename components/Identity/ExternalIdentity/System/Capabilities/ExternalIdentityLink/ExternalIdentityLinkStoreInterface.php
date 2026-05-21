<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\ExternalIdentityLink;

/**
 * Storage interface for external identity link data.
 */
interface ExternalIdentityLinkStoreInterface
{
    /**
     * @param array<string, mixed> $externalData
     */
    public function link(string $userId, string $provider, array $externalData) : void;

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(string $userId, string $provider) : array|null;
}
