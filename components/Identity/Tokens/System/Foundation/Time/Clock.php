<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tokens\System\Foundation\Time;

interface Clock
{
    public function now() : int;
}
