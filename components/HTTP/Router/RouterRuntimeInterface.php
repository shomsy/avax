<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router;

use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface;

// Backward compatibility alias — use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface instead
class_alias(
    RouterRuntimeInterface::class,
    __NAMESPACE__ . '\RouterRuntimeInterface',
);
