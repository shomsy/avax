<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Capabilities\LegacyTransfer;

use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;
use Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection;
use Avax\Components\DataStack\DataTransfer\System\Configuration\DataTransferConfig;
use Avax\Components\DataStack\DataTransfer\System\Flows\SerializeDataObject\SerializeDataObject;
use stdClass;

final readonly class SerializeLegacyDTO
{
    private SerializeDataObject $serializeDataObject;

    public function __construct()
    {
        $this->serializeDataObject = new SerializeDataObject(dataTransferConfig: DataTransferConfig::legacy());
    }

    public function toJson(object $object, ?int $flags = null, int $depth = 512) : string
    {
        $flags ??= 0;

        return $this->serializeDataObject->toJson(object: $object, flags: $flags, depth: $depth);
    }

    /**
     * @return array<string, mixed>
     */
    public function toFlatArray(object $object) : array
    {
        return $this->serializeDataObject->toFlatArray(object: $object);
    }

    public function toStdClass(object $object) : stdClass
    {
        return $this->serializeDataObject->toStdClass(object: $object);
    }

    public function toCollection(object $object) : Collection
    {
        // Global collect() helper should return Avax\Components\DataStack\Data\System\Capabilities\Forms\CollectionForm\Collection
        return new Collection(new Arrhae($this->toArray(object: $object)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $object, ?int $depth = null, bool $excludeHidden = true) : array
    {
        return $this->serializeDataObject->toArray(object: $object, depth: $depth, excludeHidden: $excludeHidden);
    }

    /**
     * @return array<string, mixed>
     */
    public function toJsonApi(object $object, string $type) : array
    {
        return $this->serializeDataObject->toJsonApi(object: $object, type: $type);
    }
}
