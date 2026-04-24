<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\SerializeDataObject;

use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\ReadDataObject\NormalizeDataObjectValue;
use Avax\DataHandling\DataTransfer\ReadDataObject\ReadDataObject;

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
