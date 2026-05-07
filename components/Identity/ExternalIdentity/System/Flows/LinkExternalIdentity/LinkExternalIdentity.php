<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Flows\LinkExternalIdentity;

final readonly class LinkExternalIdentity
{
    /**
     * @param array<string, mixed> $externalData
     *
     * @return array{userId: string, provider: string, linked: true}
     */
    public function link(string $userId, string $provider, array $externalData) : array
    {
        return [
            'userId'   => $userId,
            'provider' => $provider,
            'linked'   => true,
        ];
    }
}
