<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Flows\SerializeDataObject;

use Avax\Components\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use JsonException;

final readonly class ConvertDataObjectToJson
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    /**
     * @throws JsonException
     */
    public function convert(object $object, int|null $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;

        return json_encode(
            value: new ConvertDataObjectToArray(config: $this->config)->convert(object: $object),
            flags: $flags | JSON_THROW_ON_ERROR,
            depth: $depth,
        );
    }
}
