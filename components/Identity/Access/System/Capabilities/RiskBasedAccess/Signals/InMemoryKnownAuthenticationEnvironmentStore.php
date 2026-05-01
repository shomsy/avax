<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals;

use SensitiveParameter;

final class InMemoryKnownAuthenticationEnvironmentStore implements KnownAuthenticationEnvironmentStoreInterface
{
    /** @var array<int, array<string, true>> */
    private array $seen = [];

    public function hasSeen(int $userId, #[SensitiveParameter] ?string $ipAddress, ?string $userAgent): bool
    {
        return isset($this->seen[$userId][$this->key(ipAddress: $ipAddress, userAgent: $userAgent)]);
    }

    private function key(#[SensitiveParameter] ?string $ipAddress, ?string $userAgent): string
    {
        return strtolower(string: trim(string: $ipAddress ?? 'unknown')) . '|' . strtolower(string: trim(string: $userAgent ?? 'unknown'));
    }

    public function remember(int $userId, #[SensitiveParameter] ?string $ipAddress, ?string $userAgent): void
    {
        $this->seen[$userId][$this->key(ipAddress: $ipAddress, userAgent: $userAgent)] = true;
    }
}
