<?php

declare(strict_types=1);

use Avax\Facade\Facades\Route;
use Avax\HTTP\Request\Request;
use Avax\HTTP\Response\Classes\Response;
use Avax\HTTP\Response\Classes\Stream;
use Psr\Http\Message\ResponseInterface;

Route::get('/null-test', static function (Request $request): ?ResponseInterface {
    // This callable intentionally returns null to test fallback handling
    return null;
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