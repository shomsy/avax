<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\CreateRequestFromGlobals;

use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\ServerRequest;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;

final class CreateRequestFromGlobals
{
    public static function execute(): RequestInterface
    {
        return ServerRequest::fromGlobals();
    }
}
