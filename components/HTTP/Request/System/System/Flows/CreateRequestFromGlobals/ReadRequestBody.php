<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\System\Flows\CreateRequestFromGlobals;

final class ReadRequestBody
{
    public function read() : string
    {
        return (string) file_get_contents('php://input');
    }
}
