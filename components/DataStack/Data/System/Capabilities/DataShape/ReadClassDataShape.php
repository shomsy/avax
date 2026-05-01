<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataShape;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use ReflectionClass;

/**
 * Orchestrates the reading of class data shape by combining constructor and public fields.
 */
final readonly class ReadClassDataShape
{
    /**
     * @param class-string $class
     */
    public function read(string $class, DataTransferConfig $config): DataShape
    {
        $reflection = new ReflectionClass(objectOrClass: $class);

        $constructorFields = new ReadConstructorDataFields()->read(class: $reflection, config: $config);
        $publicFields = new ReadPublicDataFields()->read(
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
