<?php

declare(strict_types=1);

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

return static function (RouterInterface $router): void {
    $responses = new ResponseFactory();

    $router->get(path: '/health', action: static fn () => $responses->create(body: 'ok'));
};
