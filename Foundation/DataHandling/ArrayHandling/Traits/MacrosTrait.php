<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;

/**
 * Trait MacrosTrait
 *
 * Provides the ability to register and handle macros (dynamic methods) within a class.
 * This trait allows for the registration of global macros and namespaced macros,
 * enabling flexible and organized method extensions.
 *
 * The trait enforces the implementation of the `getItems` and `setItems` methods
 * in the using class to manage the underlying data collection.
 *
 * @package Avax\DataHandling\ArrayHandling\Traits
 */
trait MacrosTrait
{
    // Store macros and namespaced macros
    protected static array $macros          = [];

    protected static array $macroNamespaces = [];

    /**
     * Register a new global macro.
     *
     * This method allows you to define a macro (dynamic method) that can be called
     * on instances of the class using this trait.
     *
     * @param string  $name  The name of the macro.
     * @param Closure $macro The closure representing the macro's functionality.
     *
     *
     * @throws InvalidArgumentException If the macro name is empty or already exists.
     * @example
     * ```
     * MacrosTrait::macro('toUpperCase', function() {
     *     return array_map(fn($item) => strtoupper($item), $this->getItems());
     * });
     *
     * $instance->toUpperCase(); // Transforms all items to uppercase.
     * ```
     */
    public static function macro(string $name, Closure $macro) : void
    {
        if ($name === '' || $name === '0') {
            throw new InvalidArgumentException(message: 'Macro name cannot be empty.');
        }

        if (isset(self::$macros[$name])) {
            throw new InvalidArgumentException(message: sprintf("Macro '%s' is already registered.", $name));
        }

        self::$macros[$name] = $macro;
    }

    /**
     * Register a new namespaced macro.
     *
     * This method allows you to define a macro within a specific namespace, enabling
     * better organization and avoiding naming collisions.
     *
     * @param string  $namespace The namespace for organizing macros.
     * @param string  $name      The name of the macro within the namespace.
     * @param Closure $macro     The closure representing the macro's functionality.
     *
     *
     * @throws InvalidArgumentException If the namespace or macro name is empty or already exists.
     * @example
     * ```
     * MacrosTrait::macroNamespace('string', 'toCamelCase', function() {
     *     return array_map(fn($item) => lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $item)))),
     *     $this->getItems());
     * });
     *
     * $instance->string::toCamelCase(); // Converts snake_case strings to camelCase.
     * ```
     */
    public static function macroNamespace(string $namespace, string $name, Closure $macro) : void
    {
        if ($namespace === '' || $namespace === '0') {
            throw new InvalidArgumentException(message: 'Namespace cannot be empty.');
        }

        if ($name === '' || $name === '0') {
            throw new InvalidArgumentException(message: 'Macro name cannot be empty.');
        }

        if (isset(self::$macroNamespaces[$namespace][$name])) {
            throw new InvalidArgumentException(
                message: sprintf("Macro '%s' is already registered in namespace '%s'.", $name, $namespace)
            );
        }

        self::$macroNamespaces[$namespace][$name] = $macro;
    }

    /**
     * Dynamically handle static method calls to macros.
     *
     * This magic method intercepts static calls to methods that are not explicitly defined
     * within the class. It checks if a macro with the given name exists and invokes it.
     *
     * @param string $name      The name of the static method being called.
     * @param array  $arguments The arguments passed to the method.
     *
     * @return mixed The result of the macro invocation.
     *
     * @throws BadMethodCallException If the macro does not exist.
     *
     * @example
     * ```
     * // Static global macro
     * MacrosTrait::macro('staticMethod', function() {
     *     return 'Static method called';
     * });
     *
     * Arrhae::staticMethod(); // Returns 'Static method called'
     * ```
     */
    public static function __callStatic(string $name, array $arguments)
    {
        // Handle namespaced macros (e.g., 'namespace::macro')
        if (str_contains($name, '::')) {
            [$namespace, $macro] = explode('::', $name, 2);
            if (isset(self::$macroNamespaces[$namespace][$macro])) {
                $boundMacro = self::$macroNamespaces[$namespace][$macro]->bindTo(null, static::class);

                return call_user_func_array($boundMacro, $arguments);
            }
        }

        // Handle global macros
        if (isset(self::$macros[$name])) {
            $boundMacro = self::$macros[$name]->bindTo(null, static::class);

            return call_user_func_array($boundMacro, $arguments);
        }

        throw new BadMethodCallException(message: sprintf("Static method '%s' does not exist.", $name));
    }

    /**
     * Enforce the implementation of the getItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @return iterable The collection of items.
     */
    abstract public function getItems() : iterable;

    /**
     * Enforce the implementation of the setItems method.
     *
     * Classes using this trait must implement this method.
     *
     * @param iterable $items The collection of items to set.
     */
    abstract public function setItems(iterable $items) : static;

    /**
     * Handle dynamic method calls to macros.
     *
     * This magic method intercepts calls to methods that are not explicitly defined
     * within the class. It checks if a macro with the given name exists and invokes it.
     * It supports both global macros and namespaced macros (using the '::' separator).
     *
     * @param string $name      The name of the method being called.
     * @param array  $arguments The arguments passed to the method.
     *
     * @return mixed The result of the macro invocation.
     *
     * @throws BadMethodCallException If the macro does not exist.
     *
     * @example
     * ```
     * // Global macro
     * MacrosTrait::macro('sum', function() {
     *     return array_sum($this->getItems());
     * });
     *
     * $instance->sum(); // Returns the sum of all items.
     *
     * // Namespaced macro
     * MacrosTrait::macroNamespace('math', 'average', function() {
     *     return array_sum($this->getItems()) / count($this->getItems());
     * });
     *
     * $instance->math::average(); // Returns the average of all items.
     * ```
     */
    public function __call(string $name, array $arguments)
    {
        // Handle namespaced macros (e.g., 'namespace::macro')
        if (str_contains($name, '::')) {
            [$namespace, $macro] = explode('::', $name, 2);
            if (isset(self::$macroNamespaces[$namespace][$macro])) {
                $boundMacro = self::$macroNamespaces[$namespace][$macro]->bindTo($this, static::class);

                return call_user_func_array($boundMacro, $arguments);
            }
        }

        // Handle global macros
        if (isset(self::$macros[$name])) {
            $boundMacro = self::$macros[$name]->bindTo($this, static::class);

            return call_user_func_array($boundMacro, $arguments);
        }

        throw new BadMethodCallException(message: sprintf("Method '%s' does not exist.", $name));
    }
}
