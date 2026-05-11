<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Capabilities\RestApi;

final class RouteRegistrar
{
    /**
     * @var array<int, array{method: string, uri: string, handler: callable}>
     */
    private array $routes = [];

    public function patch(string $uri, callable $handler) : self
    {
        $this->register('PATCH', $uri, $handler);

        return $this;
    }

    private function register(string $method, string $uri, callable $handler) : void
    {
        $this->routes[] = [
            'method'  => $method,
            'uri'     => $uri,
            'handler' => $handler,
        ];
    }

    public function resource(string $baseUri, callable $index, callable|null $show = null, callable|null $store = null, callable|null $update = null, callable|null $destroy = null) : self
    {
        $this->get($baseUri, $index);

        if ($show !== null) {
            $this->get("{$baseUri}/{id}", $show);
        }

        if ($store !== null) {
            $this->post($baseUri, $store);
        }

        if ($update !== null) {
            $this->put("{$baseUri}/{id}", $update);
        }

        if ($destroy !== null) {
            $this->delete("{$baseUri}/{id}", $destroy);
        }

        return $this;
    }

    public function get(string $uri, callable $handler) : self
    {
        $this->register('GET', $uri, $handler);

        return $this;
    }

    public function post(string $uri, callable $handler) : self
    {
        $this->register('POST', $uri, $handler);

        return $this;
    }

    public function put(string $uri, callable $handler) : self
    {
        $this->register('PUT', $uri, $handler);

        return $this;
    }

    public function delete(string $uri, callable $handler) : self
    {
        $this->register('DELETE', $uri, $handler);

        return $this;
    }

    /**
     * @return array<int, array{method: string, uri: string, handler: callable}>
     */
    public function routes() : array
    {
        return $this->routes;
    }
}
