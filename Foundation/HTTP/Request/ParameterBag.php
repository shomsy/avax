<?php

declare(strict_types=1);

namespace Avax\HTTP\Request;

use InvalidArgumentException;

/**
 * Class ParameterBag
 *
 * This class is designed to handle a collection of parameters.
 * It allows for adding, removing, and retrieving parameters with type safety.
 */
class ParameterBag
{
    /**
     * ParameterBag constructor.
     *
     * Initializes the parameter bag with an optional array of parameters.
     *
     * @param array $parameters An initial set of parameters.
     */
    public function __construct(private array $parameters = []) {}

    /**
     * Check if a parameter exists by key.
     *
     * @param string $key The key to check.
     *
     * @return bool True if the key exists, false otherwise.
     */
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Get all parameters.
     *
     * @return array All parameters in the bag.
     */
    public function all() : array
    {
        return $this->parameters;
    }

    /**
     * Set a parameter by key.
     *
     * @param string $key   The key of the parameter.
     * @param mixed  $value The value to set.
     */
    public function set(string $key, mixed $value) : void
    {
        $this->parameters[$key] = $value;
    }

    /**
     * Remove a parameter by key.
     *
     * @param string $key The key of the parameter to remove.
     */
    public function remove(string $key) : void
    {
        unset($this->parameters[$key]);
    }

    /**
     * Retrieve a parameter as an array.
     *
     * @param string $key The key of the parameter.
     *
     * @return array The parameter value as an array, or an empty array if not present or not an array.
     */
    public function getAsArray(string $key) : array
    {
        return $this->getTyped(key: $key, type: 'array', default: []);
    }

    /**
     * Retrieve a parameter as a specific type.
     *
     * @param string $key     The key of the parameter.
     * @param string $type    The expected type ('string', 'int', 'bool', 'array', etc.).
     * @param mixed  $default Default value if key does not exist.
     *
     * @return mixed The parameter cast to the specified type, or default if not present.
     * @throws \InvalidArgumentException If the type is unsupported.
     */
    public function getTyped(string $key, string $type, mixed $default = null) : mixed
    {
        $value = $this->get(key: $key, default: $default);

        // Handle null and cast based on type
        if (is_null($value)) {
            return $default;
        }

        return match ($type) {
            'string' => (string) $value,
            'int'    => (int) $value,
            'float'  => (float) $value,
            'bool'   => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default,
            'array'  => is_array($value) ? $value : (array) $value,
            default  => throw new InvalidArgumentException(message: sprintf("Unsupported type '%s'", $type)),
        };
    }

    /**
     * Retrieve a parameter by key with optional default value.
     *
     * @param string $key     The key of the parameter.
     * @param mixed  $default Optional default value if key does not exist.
     *
     * @return mixed The parameter value or the default value.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    /**
     * Retrieve a parameter as a boolean.
     *
     * @param string $key     The key of the parameter.
     * @param bool   $default Default value if key does not exist.
     *
     * @return bool The parameter value as a boolean.
     */
    public function getAsBoolean(string $key, bool $default = false) : bool
    {
        return $this->getTyped(key: $key, type: 'bool', default: $default);
    }

    /**
     * Retrieve a parameter as an integer.
     *
     * @param string $key     The key of the parameter.
     * @param int    $default Default value if key does not exist.
     *
     * @return int The parameter value as an integer.
     */
    public function getAsInt(string $key, int $default = 0) : int
    {
        return $this->getTyped(key: $key, type: 'int', default: $default);
    }

    /**
     * Retrieve a parameter as a string.
     *
     * @param string $key     The key of the parameter.
     * @param string $default Default value if key does not exist.
     *
     * @return string The parameter value as a string.
     */
    public function getAsString(string $key, string $default = '') : string
    {
        return $this->getTyped(key: $key, type: 'string', default: $default);
    }
}
