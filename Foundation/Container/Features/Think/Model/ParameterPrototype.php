<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

/**
 * Immutable blueprint for a single method parameter's injection requirements.
 *
 * A ParameterPrototype captures the granular details of one single argument
 * in a method signature. It records the name, type-hint, default value,
 * nullability, and variadic status. This information is used by the
 * {@see DependencyResolver} to decide exactly what value should be passed
 * into the method at runtime.
 *
 * @see     docs/Features/Think/Model/ParameterPrototype.md
 * @see     MethodPrototype For the collection of these models.
 */
readonly class ParameterPrototype
{
    /**
     * Initializes the parameter blueprint.
     *
     * @param string      $name       The name of the variable (e.g. "logger").
     * @param string|null $type       The normalized type-hint (class name or ID).
     * @param bool        $hasDefault Whether the parameter has a ` = value` in PHP.
     * @param mixed       $default    The actual default value from the source code.
     * @param bool        $isVariadic Whether the parameter uses the `...` prefix.
     * @param bool        $allowsNull Whether the type-hint allows a null value.
     * @param bool        $required   True if no default is available and it's not nullable.
     */
    public function __construct(
        public string      $name,
        public string|null $type = null,
        public bool        $hasDefault = false,
        public mixed       $default = null,
        public bool        $isVariadic = false,
        public bool        $allowsNull = false,
        public bool        $required = true
    ) {}

    /**
     * Magic method to support serialization via var_export().
     *
     * Enables high-performance AOT compilation.
     *
     * @param array<string, mixed> $array State data for reconstruction.
     *
     * @return self The reconstructed blueprint.
     */
    public static function __set_state(array $array) : self
    {
        return self::fromArray(data: $array);
    }

    /**
     * Hydrate a parameter blueprint from a raw configuration array.
     *
     * @param array<string, mixed> $data Raw source data.
     *
     * @return self The resulting model.
     *
     * @see docs/Features/Think/Model/ParameterPrototype.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            name      : $data['name'],
            type      : $data['type'] ?? null,
            hasDefault: $data['hasDefault'] ?? false,
            default   : $data['default'] ?? null,
            isVariadic: $data['isVariadic'] ?? false,
            allowsNull: $data['allowsNull'] ?? false,
            required  : $data['required'] ?? true
        );
    }

    /**
     * Flatten the parameter blueprint into a serializable array.
     *
     * @return array<string, mixed> Descriptive metadata array.
     *
     * @see docs/Features/Think/Model/ParameterPrototype.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'name'       => $this->name,
            'type'       => $this->type,
            'hasDefault' => $this->hasDefault,
            'default'    => $this->default,
            'isVariadic' => $this->isVariadic,
            'allowsNull' => $this->allowsNull,
            'required'   => $this->required,
        ];
    }
}
