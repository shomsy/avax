<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Compatibility;

use Avax\DataFoundation\Collection;
use JsonSerializable;
use ReflectionException;
use stdClass;
use Throwable;

/**
 * @deprecated Use native constructor data objects with Avax\DataFoundation\DataTransfer\DataTransfer.
 */
abstract class LegacyAbstractDTO implements JsonSerializable
{
    /**
     * @throws ReflectionException
     */
    public function __construct(array $data = [])
    {
        $this->hydrateFrom(data: $data);
    }

    public function hydrateFrom(array $data) : void
    {
        new CreateLegacyDTO()->hydrate(target: $this, data: $data);
    }

    public function toFlatArray() : array
    {
        return new SerializeLegacyDTO()->toFlatArray(object: $this);
    }

    public function toStdClass() : stdClass
    {
        return new SerializeLegacyDTO()->toStdClass(object: $this);
    }

    public function toCollection() : Collection
    {
        return new SerializeLegacyDTO()->toCollection(object: $this);
    }

    public function toJsonApi(string $type) : array
    {
        return new SerializeLegacyDTO()->toJsonApi(object: $this, type: $type);
    }

    public function jsonSerialize() : array
    {
        return $this->toArray();
    }

    public function toArray(int|null $depth = null, bool $excludeHidden = true) : array
    {
        return new SerializeLegacyDTO()->toArray(object: $this, depth: $depth, excludeHidden: $excludeHidden);
    }

    public function __toString() : string
    {
        try {
            return $this->toJson(flags: JSON_PRETTY_PRINT);
        } catch (Throwable) {
            return '{}';
        }
    }

    public function toJson(int|null $flags = null, int $depth = 512) : string
    {
        return new SerializeLegacyDTO()->toJson(object: $this, flags: $flags ?? 0, depth: $depth);
    }
}
