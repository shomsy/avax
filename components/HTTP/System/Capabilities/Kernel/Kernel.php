<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Capabilities\Kernel;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * HTTP Kernel Interface.
 * Migrated to System Capabilities.
 */
interface Kernel
{
    public function handle(ServerRequestInterface $request) : ResponseInterface;
}
