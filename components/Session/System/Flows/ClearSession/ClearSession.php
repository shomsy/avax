<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\ClearSession;

use Avax\Components\Session\System\Capabilities\Storage\SessionScope;

final class ClearSession
{
    public function __construct(
        private readonly SessionScope $scope,
    ) {
    }

    public function clear(): void
    {
        $this->scope->clear();
    }
}