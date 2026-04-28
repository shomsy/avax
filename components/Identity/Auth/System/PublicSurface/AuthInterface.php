<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\PublicSurface;

interface AuthInterface
{
    public function user() : User|null;

    public function check() : bool;

    public function guest() : bool;
}