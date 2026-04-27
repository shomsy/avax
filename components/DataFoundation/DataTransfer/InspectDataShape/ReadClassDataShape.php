<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\InspectDataShape;

use components\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use ReflectionClass;

final readonly class ReadClassDataShape
{
    /**
     * @param class-string $class
     */
    public function read(string $class, DataTransferConfig $config) : DataShape
    {
        $reflection = new ReflectionClass(objectOrClass: $class);

        $constructorFields = new ReadConstructorDataFields()->read(class: $reflection, config: $config);
        $publicFields      = new ReadPublicDataFields()->read(
            class         : $reflection,
            config        : $config,
            existingFields: $constructorFields,
        );

        return new DataShape(
            class : $class,
            fields: [...$constructorFields, ...$publicFields],
        );
    }
}
