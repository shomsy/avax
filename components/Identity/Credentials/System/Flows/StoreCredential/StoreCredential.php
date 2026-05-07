<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Flows\StoreCredential;

final readonly class StoreCredential
{
    /**
     * @param array<string, mixed> $credentials
     *
     * @return array{userId: string, stored: true}
     */
    public function store(string $userId, array $credentials) : array
    {
        return ['userId' => $userId, 'stored' => true];
    }
}
