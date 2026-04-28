<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Flows\ReadDataObject;

use BackedEnum;
use DateTimeInterface;
use JsonSerializable;
use Stringable;
use Traversable;

final readonly class NormalizeDataObjectValue
{
    public function normalize(mixed $value, int $depth, array &$seen) : mixed
    {
        if ($depth < 0) {
            return null;
        }

        return match (true) {
            $value instanceof BackedEnum        => $value->value,
            $value instanceof DateTimeInterface => $value->format(format: DATE_ATOM),
            is_array(value: $value)             => $this->normalizeArray(value: $value, depth: $depth, seen: $seen),
            $value instanceof Traversable       => $this->normalizeArray(value: iterator_to_array(iterator: $value), depth: $depth, seen: $seen),
            is_object(value: $value)            => $this->normalizeObject(value: $value, depth: $depth, seen: $seen),
            default                             => $value,
        };
    }

    private function normalizeArray(array $value, int $depth, array &$seen) : array
    {
        $normalized = [];

        foreach ($value as $key => $item) {
            $normalized[$key] = $this->normalize(value: $item, depth: $depth - 1, seen: $seen);
        }

        return $normalized;
    }

    private function normalizeObject(object $value, int $depth, array &$seen) : mixed
    {
        $id = spl_object_id(object: $value);

        if (isset($seen[$id])) {
            return null;
        }

        $seen[$id] = true;

        if ($value instanceof JsonSerializable) {
            return $this->normalize(value: $value->jsonSerialize(), depth: $depth - 1, seen: $seen);
        }

        if ($value instanceof Stringable && $this->hasNoPublicProperties(value: $value)) {
            return (string) $value;
        }

        return $this->normalize(value: new ReadDataObject()->values(object: $value), depth: $depth - 1, seen: $seen);
    }

    private function hasNoPublicProperties(object $value) : bool
    {
        return get_object_vars(object: $value) === [];
    }
}
