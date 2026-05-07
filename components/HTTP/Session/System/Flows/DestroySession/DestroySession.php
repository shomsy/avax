<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\DestroySession;

use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;

final readonly class DestroySession
{
    public function __construct(
        private SessionScope $sessionScope,
    ) {}

    public function execute() : void
    {
        $this->sessionScope->destroy();
    }
}
