<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Exception;

final class ReportExceptionToLogger
{
    public function report(Exception $e, RequestInterface $request) : void
    {
        // Integration with Logger component would go here
    }
}
