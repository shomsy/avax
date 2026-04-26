<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\CreateDataObject;

use Avax\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataFoundation\DataTransfer\InspectDataShape\DataShape;
use Avax\DataFoundation\DataTransfer\InspectDataShape\InspectDataShape;

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
