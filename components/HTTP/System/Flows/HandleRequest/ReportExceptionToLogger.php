<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Flows\HandleRequest;

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Exception;

final class ReportExceptionToLogger
{
    public function report(Exception $exception, RequestInterface $request): void
    {
        // Integration with Logger component would go here
        error_log('HTTP Exception: '.$exception->getMessage().' for URI: '.$request->getUri());
    }
}
