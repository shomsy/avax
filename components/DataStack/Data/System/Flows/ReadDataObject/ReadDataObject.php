<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\DataShape\InspectDataShape;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;

final readonly class ReadDataObject
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    public function values(object $object, bool $excludeHidden = true) : array
    {
        $config = $this->config ?? DataTransferConfig::default();
        $shape  = new InspectDataShape(config: $config)->inspect(class: $object::class);
        $fields = new ReadVisibleDataFields()->read(shape: $shape, excludeHidden: $excludeHidden);

        return new ReadDataObjectValues()->read(object: $object, fields: $fields);
    }
}
