<?php

declare(strict_types=1);

use Avax\Components\HTTP\Request\System\PublicSurface\RequestInterface;
use Avax\Components\HTTP\Response\System\PublicSurface\Responses;
use Avax\Components\HTTP\Router\System\PublicSurface\RouterInterface;
use Psr\Http\Message\ResponseInterface;

return static function (RouterInterface $router) : void {
    $responses = app(Responses::class);

    $router->get(path: '/null-test', action: static function (RequestInterface $request) : ?ResponseInterface {
        // This callable intentionally returns null to test fallback handling
        return null;
    });

    $router->get(path: '/fallback-test', action: static function (RequestInterface $request) use ($responses) : ResponseInterface {
        $message = sprintf(
            'Route not found for [%s] %s',
            $request->getMethod(),
            $request->getUri()->getPath(),
        );

        return $responses->send(data: $message, status: 404);
    });
};
