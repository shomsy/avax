<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals;

interface KnownAuthenticationEnvironmentStoreInterface
{
    public function hasSeen(int $userId, string|null $ipAddress, string|null $userAgent) : bool;

    public function remember(int $userId, string|null $ipAddress, string|null $userAgent) : void;
}
