<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\RegenerateSession;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;

final readonly class RegenerateSession
{
    public function __construct(
        private Session $session
    ) {}

    public function handle() : void
    {
        $this->session->regenerate();
    }
}
