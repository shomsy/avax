<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\SerializeDataObject;

use Avax\Components\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use Avax\Components\Data\System\Capabilities\ObjectReading\NormalizeDataObjectValue;
use Avax\Components\Data\System\Capabilities\ObjectReading\ReadDataObject;

final readonly class ConvertDataObjectToArray
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    public function convert(object $object, int|null $depth = null, bool $excludeHidden = true) : array
    {
        $config   = $this->config ?? DataTransferConfig::default();
        $maxDepth = $depth ?? $config->maxDepth;
        $seen     = [];
        $values   = new ReadDataObject(config: $config)->values(object: $object, excludeHidden: $excludeHidden);

        return new NormalizeDataObjectValue()->normalize(value: $values, depth: $maxDepth, seen: $seen);
    }
}
