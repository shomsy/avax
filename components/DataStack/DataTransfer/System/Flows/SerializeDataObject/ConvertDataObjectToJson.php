<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use JsonException;

final readonly class ConvertDataObjectToJson
{
    public function __construct(private ?DataTransferConfig $dataTransferConfig = null) {}

    /**
     * @throws JsonException
     */
    public function convert(object $object, ?int $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;
        assert($depth >= 1);

        return json_encode(
            value: new ConvertDataObjectToArray(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object),
            flags: $flags | JSON_THROW_ON_ERROR,
            depth: $depth,
        );
    }
}
