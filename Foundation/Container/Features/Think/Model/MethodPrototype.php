<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

/**
 * Immutable blueprint for a single method's dependency injection requirements.
 *
 * A MethodPrototype encapsulates everything the container needs to know about
 * how to call a specific method (excluding the actual object instance). It
 * stores the method name and an ordered list of {@see ParameterPrototype}
 * objects. This model is used for both Constructor injection and Setter
 * injection (post-instantiation hydration).
 *
 * @see     docs/Features/Think/Model/MethodPrototype.md
 * @see     ParameterPrototype For the individual argument blueprints.
 * @see     ServicePrototype For the master blueprint that contains this model.
 */
readonly class MethodPrototype
{
    /**
     * Initializes the method blueprint.
     *
     * @param string               $name       The exact name of the method (e.g. "__construct", "setLogger").
     * @param ParameterPrototype[] $parameters Ordered list of injection requirements for arguments.
     */
    public function __construct(
        public string $name,
        public array  $parameters = []
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
     * Hydrate a method blueprint from a raw configuration array.
     *
     * @param array<string, mixed> $data Raw source data.
     *
     * @return self The resulting model.
     *
     * @see docs/Features/Think/Model/MethodPrototype.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            name      : $data['name'],
            parameters: array_map(
                static fn(array $p) : ParameterPrototype => ParameterPrototype::fromArray(data: $p),
                $data['parameters'] ?? []
            )
        );
    }

    /**
     * Flatten the method blueprint into a serializable array.
     *
     * @return array<string, mixed> Descriptive metadata array.
     *
     * @see docs/Features/Think/Model/MethodPrototype.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'name'       => $this->name,
            'parameters' => array_map(static fn(ParameterPrototype $p) : array => $p->toArray(), $this->parameters),
        ];
    }
}
