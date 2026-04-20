<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Risk;

interface KnownAuthenticationEnvironmentStoreInterface
{
    public function hasSeen(int $userId, string|null $ipAddress, string|null $userAgent) : bool;

    public function remember(int $userId, string|null $ipAddress, string|null $userAgent) : void;
}
