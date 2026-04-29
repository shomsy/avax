<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals;

final class ReadQueryParameters
{
    public function read(): array { return $_GET; }
}
