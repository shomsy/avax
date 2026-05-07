<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Flows\ClearSession;

use Avax\Components\HTTP\Session\System\System\PublicSurface\Session;

final readonly class ClearSession
{
    public function __construct(
        private Session $session,
    ) {}

    public function handle() : void
    {
        $this->session->flush();
    }
}
