<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\InspectDataShape;

use BackedEnum;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final readonly class DataFieldType
{
    /**
     * @param list<string> $names
     */
    public function __construct(
        private array $names,
        public bool   $allowsNull = true,
    ) {}

    public static function fromReflectionType(ReflectionType|null $type) : self
    {
        if ($type === null) {
            return self::mixed();
        }

        if ($type instanceof ReflectionNamedType) {
            return new self(
                names     : [$type->getName()],
                allowsNull: $type->allowsNull(),
            );
        }

        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            $names = [];

            foreach ($type->getTypes() as $nestedType) {
                if ($nestedType instanceof ReflectionNamedType) {
                    $name = $nestedType->getName();

                    if ($name !== 'null') {
                        $names[] = $name;
                    }
                }
            }

            usort(
                array   : $names,
                callback: static fn (string $left, string $right) : int => (int) class_exists(class: $right) <=> (int) class_exists(class: $left),
            );

            return new self(
                names     : $names === [] ? ['mixed'] : array_values(array: $names),
                allowsNull: $type->allowsNull(),
            );
        }

        return self::mixed();
    }

    public static function mixed() : self
    {
        return new self(names: ['mixed'], allowsNull: true);
    }

    /**
     * @return list<string>
     */
    public function names() : array
    {
        return $this->names;
    }

    public function isMixed() : bool
    {
        return $this->primaryName() === 'mixed';
    }

    public function primaryName() : string|null
    {
        return $this->names[0] ?? null;
    }

    public function isArray() : bool
    {
        return in_array(needle: 'array', haystack: $this->names, strict: true);
    }

    public function isScalar() : bool
    {
        return in_array(needle: $this->primaryName(), haystack: ['int', 'float', 'string', 'bool'], strict: true);
    }

    public function isClass() : bool
    {
        $name = $this->primaryName();

        return $name !== null && class_exists(class: $name);
    }

    public function isBackedEnum() : bool
    {
        $name = $this->primaryName();

        return $name !== null
            && enum_exists(enum: $name)
            && is_subclass_of(object_or_class: $name, class: BackedEnum::class);
    }

    public function displayName() : string
    {
        return implode(separator: '|', array: $this->names);
    }
}
