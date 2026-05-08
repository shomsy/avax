<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\Shapes\ClassShape\InspectDataShape;
use Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\Configuration\DataTransferConfig;

final readonly class ReadDataObject
{
    public function __construct(private ?DataTransferConfig $dataTransferConfig = null)
    {
    }

    public function values(object $object, bool $excludeHidden = true): array
    {
        $config = $this->dataTransferConfig ?? DataTransferConfig::default();
        $dataShape = new InspectDataShape(dataTransferConfig: $config)->inspect(class: $object::class);
        $fields = new ReadVisibleDataFields()->read(dataShape: $dataShape, excludeHidden: $excludeHidden);

        return new ReadDataObjectValues()->read(object: $object, fields: $fields);
    }
}
