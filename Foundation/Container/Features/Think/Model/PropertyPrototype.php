<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Actions\Inject\PropertyInjector;

/**
 * Immutable blueprint for a single property's dependency injection requirements.
 *
 * A PropertyPrototype describes a class variable (property) that has been
 * marked for injection (usually via the `#[Inject]` attribute). It stores
 * the property name, its required type, and fallback rules. This model
 * is used by the {@see PropertyInjector} to hydrate objects after they
 * have been instantiated.
 *
 * @see     docs/Features/Think/Model/PropertyPrototype.md
 * @see     ServicePrototype For the master blueprint that contains this model.
 */
readonly class PropertyPrototype
{
    /**
     * Initializes the property blueprint.
     *
     * @param string      $name       The name of the property (e.g. "db").
     * @param string|null $type       The normalized type-hint (class name or ID).
     * @param bool        $hasDefault Whether the property has a default value assigned.
     * @param mixed       $default    The actual default value.
     * @param bool        $allowsNull Whether the property allows a null value.
     * @param bool        $required   True if a failure to resolve should be an error.
     */
    public function __construct(
        public string      $name,
        public string|null $type = null,
        public bool        $hasDefault = false,
        public mixed       $default = null,
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
     * Hydrate a property blueprint from a raw configuration array.
     *
     * @param array<string, mixed> $data Raw source data.
     *
     * @return self The resulting model.
     *
     * @see docs/Features/Think/Model/PropertyPrototype.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            name      : $data['name'],
            type      : $data['type'] ?? null,
            hasDefault: $data['hasDefault'] ?? false,
            default   : $data['default'] ?? null,
            allowsNull: $data['allowsNull'] ?? false,
            required  : $data['required'] ?? true
        );
    }

    /**
     * Flatten the property blueprint into a serializable array.
     *
     * @return array<string, mixed> Descriptive metadata array.
     *
     * @see docs/Features/Think/Model/PropertyPrototype.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'name'       => $this->name,
            'type'       => $this->type,
            'hasDefault' => $this->hasDefault,
            'default'    => $this->default,
            'allowsNull' => $this->allowsNull,
            'required'   => $this->required,
        ];
    }
}
