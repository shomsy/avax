<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Flows\ResolveExternalIdentity;

final readonly class ResolveExternalIdentity
{
    /**
     * @param array<string, array<string, mixed>> $links
     *
     * @return array<string, mixed>|null
     */
    public function resolve(string $userId, string $provider, array $links) : ?array
    {
        return $links[$userId][$provider] ?? null;
    }
}
