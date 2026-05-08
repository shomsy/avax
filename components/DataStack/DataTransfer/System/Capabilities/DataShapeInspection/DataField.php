<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\CastWith;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\DefaultValue;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Hidden;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\ListOf;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Optional;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\AttributeReading\Required;
use ReflectionParameter;
use ReflectionProperty;

final readonly class DataField
{
    /**
     * @param object[] $attributes
     */
    public function __construct(
        public string               $name,
        public string               $inputName,
        public DataFieldType        $dataFieldType,
        public array                $attributes,
        public bool                 $isConstructorField,
        public bool                 $isPromotedProperty,
        public bool                 $isPublicProperty,
        public bool                 $hasDefaultValue,
        public mixed                $defaultValue,
        public ?ReflectionProperty  $reflectionProperty = null,
        public ?ReflectionParameter $reflectionParameter = null,
    ) {}

    public function isRequired() : bool
    {
        if ($this->hasAttribute(attributeClass: Required::class)) {
            return true;
        }

        if ($this->hasAttribute(attributeClass: Optional::class)) {
            return false;
        }

        return ! $this->hasDefaultValue && ! $this->dataFieldType->allowsNull;
    }

    public function hasAttribute(string $attributeClass) : bool
    {
        return array_any($this->attributes, fn ($attribute) : bool => $attribute instanceof $attributeClass);
    }

    public function isHidden() : bool
    {
        return $this->hasAttribute(attributeClass: Hidden::class);
    }

    public function listItemClass() : ?string
    {
        $attribute = $this->firstAttribute(attributeClass: ListOf::class);

        if ($attribute instanceof ListOf) {
            return $attribute->class;
        }

        if ($this->reflectionProperty instanceof ReflectionProperty) {
            $doc = $this->reflectionProperty->getDocComment();

            if (is_string(value: $doc) && preg_match(pattern: '/@var\s+([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\[]/', subject: $doc, matches: $matches) === 1) {
                $class = ltrim(string: $matches[1], characters: '\\');

                if (str_contains(haystack: $class, needle: '\\')) {
                    return $class;
                }

                $namespace = $this->reflectionProperty->getDeclaringClass()->getNamespaceName();

                return $namespace === '' ? $class : $namespace . '\\' . $class;
            }
        }

        return null;
    }

    public function firstAttribute(string $attributeClass) : ?object
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof $attributeClass) {
                return $attribute;
            }
        }

        return null;
    }

    public function casterClass() : ?string
    {
        $attribute = $this->firstAttribute(attributeClass: CastWith::class);

        return $attribute instanceof CastWith ? $attribute->casterClass : null;
    }

    public function defaultFromAttribute() : mixed
    {
        $attribute = $this->firstAttribute(attributeClass: DefaultValue::class);

        return $attribute instanceof DefaultValue ? $attribute->value : null;
    }

    public function hasDefaultAttribute() : bool
    {
        return $this->firstAttribute(attributeClass: DefaultValue::class) instanceof DefaultValue;
    }
}
