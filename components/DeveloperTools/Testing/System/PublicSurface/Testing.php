<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\PublicSurface;

final class Testing
{
    /**
     * @param array<string, mixed> $contracts
     *
     * @return array{verified: int, results: array<string, mixed>}
     */
    public function verifyContracts(array $contracts) : array
    {
        return ['verified' => count($contracts), 'results' => $contracts];
    }
}
