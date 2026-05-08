<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject;

use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use JsonException;
use stdClass;

final readonly class SerializeDataObject
{
    public function __construct(private ?DataTransferConfig $dataTransferConfig = null) {}

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(object $object, ?int $depth = null, bool $excludeHidden = true) : array
    {
        return new ConvertDataObjectToArray(dataTransferConfig: $this->dataTransferConfig)->convert(
            object       : $object,
            depth        : $depth,
            excludeHidden: $excludeHidden,
        );
    }

    /**
     * @throws JsonException
     */
    public function toJson(object $object, ?int $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;

        return new ConvertDataObjectToJson(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object, flags: $flags, depth: $depth);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toFlatArray(object $object) : array
    {
        return new ConvertDataObjectToFlatArray()->convert(object: $object);
    }

    /**
     * @throws JsonException
     */
    public function toStdClass(object $object) : stdClass
    {
        return new ConvertDataObjectToStdClass(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object);
    }

    /**
     * @return array{data: array{type: string, id: mixed, attributes: array<array-key, mixed>}}
     */
    public function toJsonApi(object $object, string $type) : array
    {
        return new ConvertDataObjectToJsonApi(dataTransferConfig: $this->dataTransferConfig)->convert(object: $object, type: $type);
    }
}
