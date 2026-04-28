<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use JsonException;
use stdClass;

final readonly class ConvertDataObjectToStdClass
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    /**
     * @throws JsonException
     */
    public function convert(object $object) : stdClass
    {
        return json_decode(
            json       : new ConvertDataObjectToJson(config: $this->config)->convert(object: $object),
            associative: false,
            depth      : 512,
            flags      : JSON_THROW_ON_ERROR,
        );
    }
}
