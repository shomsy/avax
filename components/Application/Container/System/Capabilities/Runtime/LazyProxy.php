<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime;

use Closure;
use WeakMap;

/**
 * Resolves one service lazily and forwards calls to the resolved instance.
 */
final class LazyProxy
{
    /** @var WeakMap<object, object> */
    private static WeakMap $instances;

    public function __construct(
        private readonly string $serviceId,
        private readonly Closure $factory,
    )
    {
        self::$instances ??= new WeakMap;
    }

    /**
     * Returns the proxied service id.
     */
    public function serviceId() : string
    {
        return $this->serviceId;
    }

    /**
     * Forwards one method call to the resolved service.
     */
    public function __call(string $name, array $arguments) : mixed
    {
        return $this->resolve()->{$name}(...$arguments);
    }

    /**
     * Resolves and returns the proxied service instance.
     */
    public function resolve() : object
    {
        if (! isset(self::$instances[$this])) {
            self::$instances[$this] = ($this->factory)();
        }

        return self::$instances[$this];
    }

    /**
     * Forwards one property read to the resolved service.
     */
    public function __get(string $name) : mixed
    {
        return $this->resolve()->{$name};
    }

    /**
     * Forwards one property write to the resolved service.
     */
    public function __set(string $name, mixed $value) : void
    {
        $this->resolve()->{$name} = $value;
    }

    /**
     * Reports whether one proxied property is set.
     */
    public function __isset(string $name) : bool
    {
        return isset($this->resolve()->{$name});
    }

    /**
     * Forwards one invocation to the resolved service.
     */
    public function __invoke(mixed ...$arguments) : mixed
    {
        return ($this->resolve())(...$arguments);
    }

    /**
     * Exposes safe debug metadata for the proxy.
     */
    public function __debugInfo() : array
    {
        return [
            'serviceId' => $this->serviceId,
            'resolved' => isset(self::$instances[$this]),
        ];
    }
}
