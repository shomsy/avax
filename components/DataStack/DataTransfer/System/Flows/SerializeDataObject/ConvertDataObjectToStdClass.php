<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use JsonException;
use stdClass;

final readonly class ConvertDataObjectToStdClass
{
    public function __construct(private DataTransferConfig|null $dataTransferConfig = null) {}

    /**
     * @throws JsonException
     */
    public function convert(object $object) : stdClass
    {
        return json_decode(
            json       : new ConvertDataObjectToJson(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object),
            associative: false,
            depth      : 512,
            flags      : JSON_THROW_ON_ERROR,
        );
    }
}
