<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\ReadDataObject\NormalizeDataObjectValue;
use Avax\Components\DataStack\DataTransfer\System\Flows\ReadDataObject\ReadDataObject;

final readonly class ConvertDataObjectToArray
{
    public function __construct(private ?DataTransferConfig $dataTransferConfig = null) {}

    /**
     * @return array<array-key, mixed>
     */
    public function convert(object $object, ?int $depth = null, bool $excludeHidden = true) : array
    {
        $config   = $this->dataTransferConfig ?? DataTransferConfig::default();
        $maxDepth = $depth ?? $config->maxDepth;
        $seen     = [];
        $values   = new ReadDataObject(dataTransferConfig: $config)->values(object: $object, excludeHidden: $excludeHidden);

        return new NormalizeDataObjectValue()->normalize(value: $values, depth: $maxDepth, seen: $seen);
    }
}
