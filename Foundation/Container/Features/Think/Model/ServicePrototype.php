<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Think\Cache\PrototypeCache;
use Avax\Container\Features\Think\Prototype\ServicePrototypeFactory;

/**
 * The immutable master blueprint for building a specific service.
 *
 * A ServicePrototype is the "Final Output" of the container's analysis phase. 
 * It contains a complete, verified description of every dependency a class 
 * needs, where they should be injected (Constructor, Properties, or Methods), 
 * and whether the class is even capable of being instantiated.
 * 
 * This object is designed to be fully serializable, perfectly suitable for 
 * being cached in files or handled by AOT (Ahead-of-Time) compilers.
 *
 * @package Avax\Container\Features\Think\Model
 * @see docs/Features/Think/Model/ServicePrototype.md
 * @see ServicePrototypeFactory For the creation logic of this model.
 * @see PrototypeCache For the persistence logic of this model.
 */
readonly class ServicePrototype
{
    /**
     * Initializes the blueprint with all discovered injection metadata.
     *
     * @param string               $class              The fully qualified name of the target class.
     * @param MethodPrototype|null $constructor        Blueprint for the object's constructor.
     * @param PropertyPrototype[]  $injectedProperties List of properties marked for dependency injection.
     * @param MethodPrototype[]    $injectedMethods    List of methods (setters) marked for dependency injection.
     * @param bool                 $isInstantiable     False if the target is an Interface or Abstract class.
     */
    public function __construct(
        public string               $class,
        public MethodPrototype|null $constructor = null,
        public array                $injectedProperties = [],
        public array                $injectedMethods = [],
        public bool                 $isInstantiable = true
    ) {}

    /**
     * Magic method to support serialization via var_export().
     *
     * This is used by the `CompiledPrototypeDumper` to create high-performance 
     * PHP cache files that can be loaded instantly in production.
     *
     * @param array<string, mixed> $array State data for reconstruction.
     * @return self The reconstructed blueprint.
     */
    public static function __set_state(array $array): self
    {
        return self::fromArray(data: $array);
    }

    /**
     * Hydrate a blueprint from a raw configuration array.
     *
     * Useful for restoring prototypes from JSON or file caches.
     *
     * @param array<string, mixed> $data Raw source data.
     * @return self The resulting model.
     *
     * @see docs/Features/Think/Model/ServicePrototype.md#method-fromarray
     */
    public static function fromArray(array $data): self
    {
        return new self(
            class: $data['class'],
            constructor: isset($data['constructor']) ? MethodPrototype::fromArray(data: $data['constructor']) : null,
            injectedProperties: array_map(
                static fn(array $p): PropertyPrototype => PropertyPrototype::fromArray(data: $p),
                $data['injectedProperties'] ?? []
            ),
            injectedMethods: array_map(
                static fn(array $m): MethodPrototype => MethodPrototype::fromArray(data: $m),
                $data['injectedMethods'] ?? []
            ),
            isInstantiable: $data['isInstantiable'] ?? true
        );
    }

    /**
     * Flatten the blueprint into a serializable array.
     *
     * @return array<string, mixed> Descriptive metadata array.
     * @see docs/Features/Think/Model/ServicePrototype.md#method-toarray
     */
    public function toArray(): array
    {
        return [
            'class'              => $this->class,
            'constructor'        => $this->constructor?->toArray(),
            'injectedProperties' => array_map(static fn(PropertyPrototype $p): array => $p->toArray(), $this->injectedProperties),
            'injectedMethods'    => array_map(static fn(MethodPrototype $m): array => $m->toArray(), $this->injectedMethods),
            'isInstantiable'     => $this->isInstantiable,
        ];
    }
}
