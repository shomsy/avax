<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals;

interface KnownAuthenticationEnvironmentStoreInterface
{
    public function hasSeen(int $userId, ?string $ipAddress, ?string $userAgent) : bool;

    public function remember(int $userId, ?string $ipAddress, ?string $userAgent) : void;
}
