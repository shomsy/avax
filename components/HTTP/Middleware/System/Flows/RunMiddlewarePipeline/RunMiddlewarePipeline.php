<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Middleware\System\Flows\RunMiddlewarePipeline;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewarePipeline;
use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;

final class RunMiddlewarePipeline
{
    public function __construct(
        private MiddlewarePipeline $pipeline,
    ) {}

    public function execute(RequestInterface $request, callable $core) : ResponseInterface
    {
        return $this->pipeline->run($request, $core);
    }
}
