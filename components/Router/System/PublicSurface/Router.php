<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\PublicSurface;

// Final class - use alias instead of extension
use Avax\Components\HTTP\Router\System\PublicSurface\Router;

class_alias(
    Router::class,
    __NAMESPACE__ . '\Router'
);
