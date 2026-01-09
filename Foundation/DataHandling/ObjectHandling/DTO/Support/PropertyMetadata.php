<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO\Support;

use ReflectionAttribute;
use ReflectionProperty;

final readonly class PropertyMetadata
{
    public function __construct(
        public string             $name,
        public ReflectionProperty $property,
        /** @var array<ReflectionAttribute> */
        public array              $attributes,
    ) {}

    /**
     * Checks whether the property has an explicit type.
     */
    public function isTyped() : bool
    {
        return $this->property->hasType();
    }

    /**
     * Checks whether the property is nullable.
     */
    public function isNullable() : bool
    {
        $type = $this->property->getType();

        return $type?->allowsNull() ?? true;
    }

    /**
     * Returns true if at least one attribute matches the given FQCN (case-sensitive).
     */
    public function hasAttribute(string $fqcn) : bool
    {
        return $this->property->getAttributes(name: $fqcn) !== [];
    }

    /**
     * Instantiates all attributes.
     *
     * @return object[] List of attribute instances.
     */
    public function instantiateAttributes() : array
    {
        return array_map(
            callback: static fn(ReflectionAttribute $attr) => $attr->newInstance(),
            array   : $this->attributes
        );
    }
}
