<?php

declare(strict_types=1);

namespace components\DataFoundation\DataTransfer\SerializeDataObject;

use components\DataFoundation\DataTransfer\Configuration\DataTransferConfig;
use JsonException;
use stdClass;

final readonly class SerializeDataObject
{
    public function __construct(private DataTransferConfig|null $config = null) {}

    public function toArray(object $object, int|null $depth = null, bool $excludeHidden = true) : array
    {
        return new ConvertDataObjectToArray(config: $this->config)->convert(
            object       : $object,
            depth        : $depth,
            excludeHidden: $excludeHidden,
        );
    }

    /**
     * @throws JsonException
     */
    public function toJson(object $object, int|null $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;

        return new ConvertDataObjectToJson(config: $this->config)->convert(object: $object, flags: $flags, depth: $depth);
    }

    public function toFlatArray(object $object) : array
    {
        return new ConvertDataObjectToFlatArray()->convert(object: $object);
    }

    /**
     * @throws JsonException
     */
    public function toStdClass(object $object) : stdClass
    {
        return new ConvertDataObjectToStdClass(config: $this->config)->convert(object: $object);
    }

    public function toJsonApi(object $object, string $type) : array
    {
        return new ConvertDataObjectToJsonApi(config: $this->config)->convert(object: $object, type: $type);
    }
}
