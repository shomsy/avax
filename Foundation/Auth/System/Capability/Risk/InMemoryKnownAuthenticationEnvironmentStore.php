<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Risk;

final class InMemoryKnownAuthenticationEnvironmentStore implements KnownAuthenticationEnvironmentStoreInterface
{
    /** @var array<int, array<string, true>> */
    private array $seen = [];

    public function hasSeen(int $userId, string|null $ipAddress, string|null $userAgent) : bool
    {
        return isset($this->seen[$userId][$this->key($ipAddress, $userAgent)]);
    }

    public function remember(int $userId, string|null $ipAddress, string|null $userAgent) : void
    {
        $this->seen[$userId][$this->key($ipAddress, $userAgent)] = true;
    }

    private function key(string|null $ipAddress, string|null $userAgent) : string
    {
        return strtolower(trim($ipAddress ?? 'unknown')) . '|' . strtolower(trim($userAgent ?? 'unknown'));
    }
}
