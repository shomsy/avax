<?php

declare(strict_types=1);

use Avax\Components\HTTP\Request\Request;
use Avax\Components\HTTP\Response\Response;
use Avax\Facade\Facades\Route;
use Psr\Http\Message\ResponseInterface;

Route::get('/null-test', static function (Request $request) : ResponseInterface|null {
    // This callable intentionally returns null to test fallback handling
    return null;
});

Route::fallback(static function (Request $request) : ResponseInterface {
    $message = sprintf(
        'Route not found for [%s] %s',
        $request->getMethod(),
        $request->getUri()->getPath(),
    );

    return Response::text(content: $message, status: 404);
});
