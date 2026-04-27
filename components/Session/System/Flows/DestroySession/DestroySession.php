<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\DestroySession;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

final class DestroySession
{
    public function __construct(
        private readonly SessionStore $store,
    ) {
    }

    public function destroy(): void
    {
        $this->store->destroy();
    }
}