<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Flows\ForgetSessionValue;

use Avax\Components\HTTP\Session\System\PublicSurface\Session;

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
