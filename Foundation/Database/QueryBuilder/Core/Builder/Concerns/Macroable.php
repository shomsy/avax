<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Builder\Concerns;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Trait Macroable
 *
 * -- intent: enable runtime extension of the builder through dynamic method registration.
 */
trait Macroable
{
    // Global registry for dynamic builder macros
    protected static array $macros = [];

    /**
     * Register multiple macros at once from a specific mixin class.
     *
     * -- intent: facilitate bulk extension from external provider classes.
     *
     * @param object|string $mixin Target class containing custom methods
     *
     * @return void
     * @throws ReflectionException If class analysis fails
     */
    public static function mixin(object|string $mixin) : void
    {
        $methods = (new ReflectionClass(objectOrClass: $mixin))->getMethods(
            filter: ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            $method->setAccessible(accessible: true);
            static::macro(name: $method->name, macro: $method->invoke(object: $mixin));
        }
    }

    /**
     * Register a new custom macro (dynamic method).
     *
     * -- intent: provide a way to inject domain-specific helpers into the fluent API.
     *
     * @param string          $name  Method technical name
     * @param callable|object $macro Implementation closure or invokable object
     *
     * @return void
     */
    public static function macro(string $name, callable|object $macro) : void
    {
        static::$macros[$name] = $macro;
    }

    /**
     * Handle dynamic calls to macros or trigger standard failure.
     *
     * -- intent: automate the execution of injected methods via magic interceptor.
     *
     * @param string $method     Target method name
     * @param array  $parameters Call arguments
     *
     * @return mixed
     * @throws BadMethodCallException If method is not found in macros or class
     */
    public function __call(string $method, array $parameters) : mixed
    {
        if (! static::hasMacro(name: $method)) {
            throw new BadMethodCallException(message: "Method [{$method}] does not exist on " . static::class);
        }

        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            return call_user_func_array(
                callback: $macro->bindTo(newThis: $this, newscope: static::class),
                args    : $parameters
            );
        }

        return call_user_func_array(callback: $macro, args: $parameters);
    }

    /**
     * Verify if a specific macro name has been registered.
     *
     * -- intent: provide a way to check for feature existence at runtime.
     *
     * @param string $name Method name to check
     *
     * @return bool
     */
    public static function hasMacro(string $name) : bool
    {
        return isset(static::$macros[$name]);
    }
}
