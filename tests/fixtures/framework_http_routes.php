<?php

declare(strict_types=1);

use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

return static function (RouterInterface $router): void {
    $router->get(path: '/health', action: static fn () => 'ok');
};
