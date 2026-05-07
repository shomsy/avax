<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\System\Capabilities\Kernel;

use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;

final class TerminateHttpKernel
{
    public function terminate(RequestInterface $request, ResponseInterface $response) : void
    {
        // Termination logic
    }
}
