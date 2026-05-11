<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteMethod;

final readonly class RouteDefinition
{
    /** @var list<string|callable> */
    private array $middleware;

    /**
     * @param list<string|callable> $middleware
     */
    public function __construct(
        private RouteMethod $method,
        private string      $uri,
        private mixed       $action,
        private string $name = '',
        array          $middleware = [],
    )
    {
        /** @var list<string|callable> $middleware */
        $this->middleware = $middleware;
    }

    public function method() : RouteMethod
    {
        return $this->method;
    }

    public function uri() : string
    {
        return $this->uri;
    }

    public function action() : mixed
    {
        return $this->action;
    }

    public function name() : string
    {
        return $this->name;
    }

    /** @return list<string|callable> */
    public function middleware() : array
    {
        return $this->middleware;
    }

    public function withName(string $name) : self
    {
        return new self(
            method    : $this->method,
            uri       : $this->uri,
            action    : $this->action,
            name      : $name,
            middleware: $this->middleware,
        );
    }

    /** @param list<string|callable> $middleware */
    public function withMiddleware(array $middleware) : self
    {
        return new self(
            method    : $this->method,
            uri       : $this->uri,
            action    : $this->action,
            name      : $this->name,
            middleware: $middleware,
        );
    }
}
