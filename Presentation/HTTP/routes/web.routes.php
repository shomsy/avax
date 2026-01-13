<?php

declare(strict_types=1);

use Avax\Facade\Facades\Route;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Psr\Http\Message\ResponseInterface;

Route::get('/', static function (Request $request) : ResponseInterface {
    $body = 'HTTP Foundation v2.0 - Router is Working!';

    return new Response(Stream::fromString($body), null, 200, ['Content-Type' => 'text/plain']);
})->name(name: 'home');

Route::get('/health', static function (Request $request) : ResponseInterface {
    $body = 'ok';

    return new Response(
        stream    : Stream::fromString(content: $body),
        statusCode: 200,
        headers   : ['Content-Type' => 'text/plain'],
    );
})->name(name: 'health');

Route::get('/test', static function (Request $request) : ResponseInterface {
    $body = 'Test route - Enterprise Router Active! =�';

    return new Response(
        stream    : Stream::fromString(content: $body),
        statusCode: 200,
        headers   : ['Content-Type' => 'text/plain'],
    );
})->name(name: 'test');

Route::get('/favicon.ico', static function (Request $request) : ResponseInterface {
    error_log("Favicon route called");
    return new Response(
        stream    : Stream::fromString(content: ''),
        statusCode: 204,
        headers   : ['Content-Type' => 'image/x-icon'],
    );
});

Route::fallback(static function (Request $request) : ResponseInterface {
    $message = sprintf(
        'Route not found for [%s] %s',
        $request->getMethod(),
        $request->getUri()->getPath()
    );
    error_log("Fallback called, returning message: " . $message);
    return new Response(
        stream: Stream::fromString(content: $message),
        statusCode: 404,
        headers: ['Content-Type' => 'text/plain'],
    );
});