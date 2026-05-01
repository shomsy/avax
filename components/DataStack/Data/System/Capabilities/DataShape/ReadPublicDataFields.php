<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataShape;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Capabilities\FieldMapping\ReadMappedInputName;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use ReflectionClass;
use ReflectionProperty;

/**
 * Inspects public properties to identify DTO fields.
 */
final readonly class ReadPublicDataFields
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     * @param array<string, DataField> $existingFields
     *
     * @return array<string, DataField>
     */
    public function read(ReflectionClass $reflectionClass, DataTransferConfig $dataTransferConfig, array $existingFields = []) : array
    {
        if (! $dataTransferConfig->allowPublicPropertyHydration) {
            return [];
        }

        $fields = [];

        foreach ($reflectionClass->getProperties(filter: ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            if (array_key_exists(key: $reflectionProperty->getName(), array: $existingFields)) {
                continue;
            }

            $attributes = new ReadDataFieldAttributes()->read(property: $reflectionProperty);
            $inputName  = new ReadMappedInputName()->read(
                fieldName : $reflectionProperty->getName(),
                attributes: $attributes,
                config    : $dataTransferConfig,
            );

            $fields[$reflectionProperty->getName()] = new DataField(
                name              : $reflectionProperty->getName(),
                inputName         : $inputName->value,
                attributes        : $attributes,
                isConstructorField: false,
                isPromotedProperty: false,
                isPublicProperty  : true,
                hasDefaultValue   : $reflectionProperty->hasDefaultValue(),
                defaultValue      : $reflectionProperty->hasDefaultValue() ? $reflectionProperty->getDefaultValue() : null,
                type              : DataFieldType::fromReflectionType(type: $reflectionProperty->getType()),
                property          : $reflectionProperty,
            );
        }

        return $fields;
    }
}
