<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\System\Flows\RunMiddlewarePipeline;

use Avax\Components\HTTP\Middleware\System\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Request\System\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\System\PublicSurface\ResponseInterface;

final readonly class RunMiddlewarePipeline
{
    public function __construct(
        private MiddlewarePipeline $middlewarePipeline,
    ) {}

    public function execute(RequestInterface $request, callable $core) : ResponseInterface
    {
        return $this->middlewarePipeline->run($request, $core);
    }
}
