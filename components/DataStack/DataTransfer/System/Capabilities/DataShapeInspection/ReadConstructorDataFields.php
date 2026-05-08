<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\FieldMapping\ReadMappedInputName;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\LegacyTransfer\AbstractDTO;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use ReflectionClass;
use ReflectionException;

/**
 * Inspects class constructor to identify DTO fields.
 */
final readonly class ReadConstructorDataFields
{
    /**
     * @param ReflectionClass<object> $reflectionClass
     *
     * @return array<string, DataField>
     *
     * @throws ReflectionException
     */
    public function read(ReflectionClass $reflectionClass, DataTransferConfig $dataTransferConfig) : array
    {
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return [];
        }

        // Special case for legacy DTOs that might have a base constructor we want to ignore
        if (
            is_a(object_or_class: $reflectionClass->getName(), class: AbstractDTO::class, allow_string: true)
            && $constructor->getDeclaringClass()->getName() === AbstractDTO::class
        ) {
            return [];
        }

        $fields = [];

        foreach ($constructor->getParameters() as $reflectionParameter) {
            $property = null;

            if ($reflectionParameter->isPromoted() && $reflectionClass->hasProperty(name: $reflectionParameter->getName())) {
                $property = $reflectionClass->getProperty(name: $reflectionParameter->getName());
            }

            $attributes = new ReadDataFieldAttributes()->read(reflectionProperty: $property, reflectionParameter: $reflectionParameter);
            $inputName  = new ReadMappedInputName()->read(
                fieldName         : $reflectionParameter->getName(),
                attributes        : $attributes,
                dataTransferConfig: $dataTransferConfig,
            );

            $fields[$reflectionParameter->getName()] = new DataField(
                name               : $reflectionParameter->getName(),
                inputName          : $inputName->value,
                attributes         : $attributes,
                isConstructorField : true,
                isPromotedProperty : $reflectionParameter->isPromoted(),
                isPublicProperty   : $property?->isPublic() ?? false,
                hasDefaultValue    : $reflectionParameter->isDefaultValueAvailable(),
                defaultValue       : $reflectionParameter->isDefaultValueAvailable() ? $reflectionParameter->getDefaultValue() : null,
                dataFieldType      : DataFieldType::fromReflectionType(type: $reflectionParameter->getType()),
                reflectionProperty : $property,
                reflectionParameter: $reflectionParameter,
            );
        }

        return $fields;
    }
}
