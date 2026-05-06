<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals;

final class ReadServerParameters
{
    public function read(): array
    {
        return $_SERVER;
    }
}
