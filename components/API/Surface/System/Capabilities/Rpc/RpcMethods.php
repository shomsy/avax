<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Rpc;

use Avax\Components\API\Surface\System\Foundation\Failure\RpcMethodNotFound\RpcMethodNotFound;
use Closure;

final class RpcMethods
{
    /**
     * @var array<string, Closure>
     */
    private array $methods = [];

    public function register(string $name, Closure $handler) : self
    {
        $this->methods[$name] = $handler;

        return $this;
    }

    public function unregister(string $name) : self
    {
        unset($this->methods[$name]);

        return $this;
    }

    public function exists(string $name) : bool
    {
        return isset($this->methods[$name]);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return mixed
     */
    public function call(string $name, array $params = []) : mixed
    {
        $handler = $this->methods[$name] ?? null;

        if ($handler === null) {
            throw new RpcMethodNotFound("RPC method '{$name}' is not registered.");
        }

        return $handler($params);
    }

    /**
     * @return array<string, Closure>
     */
    public function methods() : array
    {
        return $this->methods;
    }
}
