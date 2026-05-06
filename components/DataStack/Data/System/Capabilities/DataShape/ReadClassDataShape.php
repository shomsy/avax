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
     * @param  class-string  $class
     */
    public function read(string $class, DataTransferConfig $dataTransferConfig): DataShape
    {
        $reflectionClass = new ReflectionClass(objectOrClass: $class);

        $constructorFields = new ReadConstructorDataFields()->read(class: $reflectionClass, config: $dataTransferConfig);
        $publicFields = new ReadPublicDataFields()->read(
            existingFields: $constructorFields,
            class         : $reflectionClass,
            config        : $dataTransferConfig,
        );

        return new DataShape(
            class : $class,
            fields: [...$constructorFields, ...$publicFields],
        );
    }
}
