<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\InspectDataShape;

use components\DataFoundation\DataTransfer\Capabilities\FieldMapping\ReadMappedInputName;
use components\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use ReflectionClass;
use ReflectionProperty;

final readonly class ReadPublicDataFields
{
    /**
     * @param ReflectionClass<object>  $class
     * @param array<string, DataField> $existingFields
     *
     * @return array<string, DataField>
     */
    public function read(ReflectionClass $class, DataTransferConfig $config, array $existingFields = []) : array
    {
        if (! $config->allowPublicPropertyHydration) {
            return [];
        }

        $fields = [];

        foreach ($class->getProperties(filter: ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() || array_key_exists(key: $property->getName(), array: $existingFields)) {
                continue;
            }

            $attributes = new ReadDataFieldAttributes()->read(property: $property);
            $inputName  = new ReadMappedInputName()->read(
                fieldName : $property->getName(),
                attributes: $attributes,
                config    : $config,
            );

            $fields[$property->getName()] = new DataField(
                name              : $property->getName(),
                inputName         : $inputName->value,
                type              : DataFieldType::fromReflectionType(type: $property->getType()),
                attributes        : $attributes,
                isConstructorField: false,
                isPromotedProperty: false,
                isPublicProperty  : true,
                hasDefaultValue   : $property->hasDefaultValue(),
                defaultValue      : $property->hasDefaultValue() ? $property->getDefaultValue() : null,
                property          : $property,
            );
        }

        return $fields;
    }
}
