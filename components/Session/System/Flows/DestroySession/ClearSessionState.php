<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\DestroySession;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

final class ClearSessionState
{
    public function __construct(
        private readonly SessionStore $store,
    ) {
    }

    public function clear(): void
    {
        $this->store->current()->clear();
    }
}