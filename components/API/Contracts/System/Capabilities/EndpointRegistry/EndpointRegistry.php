<?php

declare(strict_types=1);

namespace Avax\Components\API\Contracts\System\Capabilities\EndpointRegistry;

final class EndpointRegistry
{
    /** @var list<array{method:string,path:string,version:string,deprecated:bool}> */
    private array $endpoints = [];

    public function register(string $method, string $path, string $version = '1.0.0', bool $deprecated = false) : self
    {
        $this->endpoints[] = [
            'method'     => strtoupper(string: $method),
            'path'       => $path,
            'version'    => $version,
            'deprecated' => $deprecated,
        ];

        return $this;
    }

    /**
     * @return list<array{method:string,path:string,version:string,deprecated:bool}>
     */
    public function all() : array
    {
        return $this->endpoints;
    }

    /**
     * @return list<array{method:string,path:string,version:string,deprecated:bool}>
     */
    public function findByPath(string $path) : array
    {
        return array_values(array_filter(
                                array   : $this->endpoints,
                                callback: static fn (array $endpoint) : bool => $endpoint['path'] === $path
                            ));
    }
}
