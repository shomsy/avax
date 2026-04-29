<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

interface AuthInterface
{
    public function user() : ?object;

    public function check() : bool;

    public function guest() : bool;
}