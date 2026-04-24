<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Compatibility;

use Avax\DataHandling\DataTransfer\Configuration\DataTransferConfig;
use Avax\DataHandling\DataTransfer\SerializeDataObject\SerializeDataObject;
use Avax\DataFoundation\Collection;
use stdClass;

final readonly class SerializeLegacyDTO
{
    private SerializeDataObject $serializer;

    public function __construct()
    {
        $this->serializer = new SerializeDataObject(config: DataTransferConfig::legacy());
    }

    public function toArray(object $object, int|null $depth = null, bool $excludeHidden = true) : array
    {
        return $this->serializer->toArray(object: $object, depth: $depth, excludeHidden: $excludeHidden);
    }

    public function toJson(object $object, int|null $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;

        return $this->serializer->toJson(object: $object, flags: $flags, depth: $depth);
    }

    public function toFlatArray(object $object) : array
    {
        return $this->serializer->toFlatArray(object: $object);
    }

    public function toStdClass(object $object) : stdClass
    {
        return $this->serializer->toStdClass(object: $object);
    }

    public function toCollection(object $object) : Collection
    {
        return collect(items: $this->toArray(object: $object));
    }

    public function toJsonApi(object $object, string $type) : array
    {
        return $this->serializer->toJsonApi(object: $object, type: $type);
    }
}
