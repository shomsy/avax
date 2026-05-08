<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\ReadDataObject;

use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;

final readonly class ReadDataObject
{
    public function __construct(private ?DataTransferConfig $dataTransferConfig = null) {}

    /**
     * @return array<array-key, mixed>
     */
    public function values(object $object, bool $excludeHidden = true) : array
    {
        $config    = $this->dataTransferConfig ?? DataTransferConfig::default();
        $dataShape = new InspectDataShape(dataTransferConfig: $config)->inspect(class: $object::class);
        $fields    = new ReadVisibleDataFields()->read(dataShape: $dataShape, excludeHidden: $excludeHidden);

        return new ReadDataObjectValues()->read(object: $object, fields: $fields);
    }
}
