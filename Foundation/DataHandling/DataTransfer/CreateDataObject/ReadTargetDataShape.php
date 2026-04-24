<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\CreateDataObject;

use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\InspectDataShape\DataShape;
use Avax\DataHandling\DataTransfer\InspectDataShape\InspectDataShape;

final readonly class ReadTargetDataShape
{
    public function __construct(private DataTransferConfig $config) {}

    /**
     * @param class-string $class
     */
    public function read(string $class) : DataShape
    {
        return new InspectDataShape(config: $this->config)->inspect(class: $class);
    }
}
