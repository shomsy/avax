<?php

declare(strict_types=1);

use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Router\Routing\RouteBuilder;
use Avax\HTTP\Router\Support\RouteCollector;
use Psr\Http\Message\ResponseInterface;

RouteCollector::add(
    builder: RouteBuilder::make(method: 'GET', path: '/')
        ->action(action: static function (Request $request): ResponseInterface {
            $body = 'Avax components router is up.';

            return new Response(
                stream: Stream::fromString(content: $body),
                statusCode: 200,
                headers: ['Content-Type' => 'text/plain'],
            );
        })
        ->name(name: 'home')
);

RouteCollector::add(
    builder: RouteBuilder::make(method: 'GET', path: '/health')
        ->action(action: static function (Request $request): ResponseInterface {
            $body = 'ok';

            return new Response(
                stream: Stream::fromString(content: $body),
                statusCode: 200,
                headers: ['Content-Type' => 'text/plain'],
            );
        })
        ->name(name: 'health')
);

RouteCollector::add(
    builder: RouteBuilder::make(method: 'GET', path: '/favicon.ico')
        ->action(action: static function (): ResponseInterface {
            return new Response(
                stream: Stream::fromString(content: ''),
                statusCode: 204,
                headers: ['Content-Type' => 'image/x-icon'],
            );
        })
);

RouteCollector::fallback(
    handler: static function (Request $request): ResponseInterface {
        throw \Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException::for(
            method: $request->getMethod(),
            path: $request->getUri()->getPath()
        );
    }
);
