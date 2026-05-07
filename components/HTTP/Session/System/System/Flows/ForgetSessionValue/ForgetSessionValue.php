<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Flows\ForgetSessionValue;

use Avax\Components\HTTP\Session\System\System\PublicSurface\Session;

final readonly class ForgetSessionValue
{
    public function __construct(
        private Session $session,
    ) {}

    public function handle(string $key) : void
    {
        $this->session->forget($key);
    }
}
