<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\System\Configuration;

use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;
use LogicException;

/**
 * RouterBootstrapper — builds the router runtime for the App kernel.
 *
 * V5-15/16: Filled from empty placeholder with original bootstrap behavior.
 */
final readonly class RouterBootstrapper
{
    public function __construct(
        private RouterRuntimeInterface|null $routerRuntime = null,
    ) {}

    public function bootstrap(): RouterRuntimeInterface
    {
        if (! $this->routerRuntime instanceof RouterRuntimeInterface) {
            throw new LogicException(
                'RouterBootstrapper requires a runtime router instance to build an AppKernel.',
            );
        }

        return $this->routerRuntime;
    }
}
