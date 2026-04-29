<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router;

// Re-export from canonical location for backward compatibility
class_alias(
    System\PublicSurface\RouterRuntimeInterface::class,
    __NAMESPACE__ . '\RouterRuntimeInterface'
);

/**
 * @deprecated Use Avax\Components\HTTP\Router\System\PublicSurface\RouterRuntimeInterface
 */
interface RouterRuntimeInterface extends System\PublicSurface\RouterRuntimeInterface {}
