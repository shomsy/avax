<?php

declare(strict_types=1);

use Avax\Facade\Facades\Route;
use Avax\HTTP\Response\Response;
use Psr\Http\Message\ServerRequestInterface;

/**
 * --------------------------------------------------------------------------
 * Web Routes
 * --------------------------------------------------------------------------
 *
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 *
 */

// Basic system routes using lean closure actions
Route::get('/', static fn () => 'HTTP Foundation v2.0 - Router is Working!')
    ->name('home');

Route::get('/health', static fn () => 'ok')
    ->name('health');

Route::get('/test', static fn () => 'Test route - Enterprise Router Active! = ')
    ->name('test');

// Dedicated assets/utility routes
Route::get('/favicon.ico', static fn () => Response::noContent([
                                                                   'Content-Type' => 'image/x-icon'
                                                               ]));

// Global fallback handler for unmatched routes
// Using PSR-7 ServerRequestInterface for maximum compatibility and stability
Route::fallback(static fn (ServerRequestInterface $request) => Response::text(
    content: sprintf(
        'Route not found for [%s] %s',
        $request->getMethod(),
        $request->getUri()->getPath()
             ),
    status : 404
));