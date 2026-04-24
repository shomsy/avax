<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\InspectDataShape;

use Avax\DataHandling\DataTransfer\Capabilities\FieldMapping\ReadMappedInputName;
use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\Compatibility\LegacyAbstractDTO;
use ReflectionClass;
use ReflectionException;

final readonly class ReadConstructorDataFields
{
    /**
     * @param ReflectionClass<object> $class
     *
     * @return array<string, DataField>
     *
     * @throws ReflectionException
     */
    public function read(ReflectionClass $class, DataTransferConfig $config) : array
    {
        $constructor = $class->getConstructor();

        if ($constructor === null) {
            return [];
        }

        if (
            is_a(object_or_class: $class->getName(), class: LegacyAbstractDTO::class, allow_string: true)
            && $constructor->getDeclaringClass()->getName() === LegacyAbstractDTO::class
        ) {
            return [];
        }

        $fields = [];

        foreach ($constructor->getParameters() as $parameter) {
            $property = null;

            if ($parameter->isPromoted() && $class->hasProperty(name: $parameter->getName())) {
                $property = $class->getProperty(name: $parameter->getName());
            }

            $attributes = new ReadDataFieldAttributes()->read(property: $property, parameter: $parameter);
            $inputName  = new ReadMappedInputName()->read(
                fieldName : $parameter->getName(),
                attributes: $attributes,
                config    : $config,
            );

            $fields[$parameter->getName()] = new DataField(
                name              : $parameter->getName(),
                inputName         : $inputName->value,
                type              : DataFieldType::fromReflectionType(type: $parameter->getType()),
                attributes        : $attributes,
                isConstructorField: true,
                isPromotedProperty: $parameter->isPromoted(),
                isPublicProperty  : $property?->isPublic() ?? false,
                hasDefaultValue   : $parameter->isDefaultValueAvailable(),
                defaultValue      : $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                property          : $property,
                parameter         : $parameter,
            );
        }

        return $fields;
    }
}
