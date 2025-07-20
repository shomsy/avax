<?php

declare(strict_types=1);

namespace Gemini\Facade;

use RuntimeException;

/**
 * Abstract base class for creating facades that provide static access to services.
 * Facades implemented using this base class allow methods to be called statically,
 * delegating the calls to the resolved instance of the service.
 */
abstract class BaseFacade
{
    /**
     * The key used to resolve the service instance from the container.
     * Concrete facades should override this property with the appropriate service key.
     */
    protected static string $accessor;

    /**
     * Handles static calls to the facade and delegates them to the resolved service instance.
     *
     * @param string $methodName The name of the method being called.
     * @param array  $arguments  Arguments passed to the method.
     *
     * @return mixed The result of the method call.
     *
     * @throws RuntimeException if the method does not exist on the service instance.
     */
    public static function __callStatic(string $methodName, array $arguments) : mixed
    {
        $instance = static::resolveFacadeInstance();

        if (! is_callable([$instance, $methodName])) {
            throw new RuntimeException(
                sprintf("Method '%s' does not exist or is not callable on the facade '%s'.", $methodName, static::class)
            );
        }

        return $instance->{$methodName}(...$arguments);
    }

    /**
     * Resolves the instance of the service being facade.
     *
     * @return mixed Resolved service instance.
     *
     * @throws RuntimeException if the service does not exist in the container.
     */
    protected static function resolveFacadeInstance() : mixed
    {
        if (! isset(static::$accessor) || (static::$accessor === '' || static::$accessor === '0')) {
            throw new RuntimeException(
                sprintf("The facade '%s' must define a non-empty static accessor property.", static::class)
            );
        }

        if (! app()->has(static::$accessor)) {
            throw new RuntimeException(
                sprintf("Service '%s' not found in the container.", static::$accessor)
            );
        }

        return app(static::$accessor);
    }
}