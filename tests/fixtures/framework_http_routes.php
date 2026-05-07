<?php

declare(strict_types=1);

use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;

return static function (RouterInterface $router): void {
    $responses = new Responses();

    $router->get(path: '/health', action: static fn () => $responses->send(data: 'ok'));
};
