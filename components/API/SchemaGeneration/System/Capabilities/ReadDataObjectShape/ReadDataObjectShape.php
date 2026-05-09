<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\DataShape;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\PublicSurface\DataObject;

/**
 * Reads the shape of a DataObject using existing InspectDataShape capability.
 */
final readonly class ReadDataObjectShape
{
    public function __construct(
        private InspectDataShape $inspector = new InspectDataShape(),
    ) {}

    /**
     * @template T of DataObject
     * @param class-string<T>|T $objectOrClass
     */
    public function read(string|object $objectOrClass) : DataShape
    {
        $class = is_string(value: $objectOrClass) ? $objectOrClass : $objectOrClass::class;

        return $this->inspector->inspect(class: $class);
    }
}
