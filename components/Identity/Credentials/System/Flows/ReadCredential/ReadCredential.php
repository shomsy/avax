<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Flows\ReadCredential;

final readonly class ReadCredential
{
    /**
     * @param array<string, mixed> $store
     *
     * @return array<string, mixed>|null
     */
    public function read(string $userId, array $store) : array|null
    {
        return $store[$userId] ?? null;
    }
}
