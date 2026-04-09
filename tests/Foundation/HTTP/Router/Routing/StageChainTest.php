<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests\Unit;

use Avax\HTTP\Request\Request;
use Avax\HTTP\Router\Routing\RouteStage;
use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class FakeContainer implements PsrContainerInterface
{
    public function get(string $id) : mixed
    {
        return new $id;
    }

    public function has(string $id) : bool
    {
        return class_exists($id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function call(callable|string $callable, array $parameters = []) : mixed
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function injectInto(object $target) : object
    {
        throw new RuntimeException(message: 'Not implemented.');
    }

    public function canInject(object $target) : bool
    {
        return false;
    }

    public function beginScope() : void {}

    public function endScope() : void {}

    public function instance(string $abstract, object $instance) : void
    {
        throw new RuntimeException(message: 'Not implemented.');
    }
}

final class SampleStage implements RouteStage
{
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}

final class SampleMiddleware
{
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        return $next($request);
    }
}
