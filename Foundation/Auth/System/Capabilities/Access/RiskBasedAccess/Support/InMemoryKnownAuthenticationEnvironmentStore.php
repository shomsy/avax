<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

use SensitiveParameter;

final class InMemoryKnownAuthenticationEnvironmentStore implements KnownAuthenticationEnvironmentStoreInterface
{
    /** @var array<int, array<string, true>> */
    private array $seen = [];

    public function hasSeen(int $userId, #[SensitiveParameter] string|null $ipAddress, string|null $userAgent) : bool
    {
        return isset($this->seen[$userId][$this->key(ipAddress: $ipAddress, userAgent: $userAgent)]);
    }

    private function key(#[SensitiveParameter] string|null $ipAddress, string|null $userAgent) : string
    {
        return strtolower(trim($ipAddress ?? 'unknown')) . '|' . strtolower(trim($userAgent ?? 'unknown'));
    }

    public function remember(int $userId, #[SensitiveParameter] string|null $ipAddress, string|null $userAgent) : void
    {
        $this->seen[$userId][$this->key(ipAddress: $ipAddress, userAgent: $userAgent)] = true;
    }
}
