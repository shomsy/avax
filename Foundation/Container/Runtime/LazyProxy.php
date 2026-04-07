<?php

declare(strict_types=1);

namespace Avax\Container\Runtime;

use Closure;

final class LazyProxy
{
    /** @var WeakMap<object, object> */
    private static \WeakMap $instances;

    public function __construct(
        private readonly string $serviceId,
        private readonly Closure $factory
    ) {
        self::$instances ??= new \WeakMap();
    }

    public function resolve() : object
    {
        if (! isset(self::$instances[$this])) {
            self::$instances[$this] = ($this->factory)();
        }

        return self::$instances[$this];
    }

    public function serviceId() : string
    {
        return $this->serviceId;
    }

    public function __call(string $name, array $arguments) : mixed
    {
        return $this->resolve()->{$name}(...$arguments);
    }

    public function __get(string $name) : mixed
    {
        return $this->resolve()->{$name};
    }

    public function __set(string $name, mixed $value) : void
    {
        $this->resolve()->{$name} = $value;
    }

    public function __isset(string $name) : bool
    {
        return isset($this->resolve()->{$name});
    }

    public function __invoke(mixed ...$arguments) : mixed
    {
        return ($this->resolve())(...$arguments);
    }

    public function __debugInfo() : array
    {
        return [
            'serviceId' => $this->serviceId,
            'resolved' => isset(self::$instances[$this]),
        ];
    }
}
