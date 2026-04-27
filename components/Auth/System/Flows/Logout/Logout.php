<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\Logout;

use Avax\Components\Auth\System\PublicSurface\Auth;

final class Logout
{
    public function __construct(
        private readonly Auth $auth,
    ) {
    }

    public function logout(): void
    {
    }
}