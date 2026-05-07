<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\System\Flows\StoreSessionValue;

use Avax\Components\HTTP\Session\System\System\PublicSurface\Session;

final readonly class StoreSessionValue
{
    public function __construct(
        private Session $session,
    ) {}

    public function handle(string $key, mixed $value) : void
    {
        $this->session->put($key, $value);
    }
}
