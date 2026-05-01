<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Compatibility;

use Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection;
use Avax\Components\DataStack\Data\System\Capabilities\DataTransfer\Configuration\DataTransferConfig;
use Avax\Components\DataStack\Data\System\Flows\SerializeDataObject\SerializeDataObject;
use stdClass;

final readonly class SerializeLegacyDTO
{
    private SerializeDataObject $serializeDataObject;

    public function __construct()
    {
        $this->serializeDataObject = new SerializeDataObject(config: DataTransferConfig::legacy());
    }

    public function toJson(object $object, ?int $flags = null, int $depth = 512): string
    {
        $flags ??= 0;

        return $this->serializeDataObject->toJson(object: $object, flags: $flags, depth: $depth);
    }

    public function toFlatArray(object $object): array
    {
        return $this->serializeDataObject->toFlatArray(object: $object);
    }

    public function toStdClass(object $object): stdClass
    {
        return $this->serializeDataObject->toStdClass(object: $object);
    }

    public function toCollection(object $object): Collection
    {
        // Global collect() helper should return Avax\Components\DataStack\Data\System\Capabilities\Collections\Collection
        return collect(items: $this->toArray(object: $object));
    }

    public function toArray(object $object, ?int $depth = null, bool $excludeHidden = true): array
    {
        return $this->serializeDataObject->toArray(object: $object, depth: $depth, excludeHidden: $excludeHidden);
    }

    public function toJsonApi(object $object, string $type): array
    {
        return $this->serializeDataObject->toJsonApi(object: $object, type: $type);
    }
}
