<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Interop\Json;

use Avax\DataFoundation\Collection;
use Avax\DataFoundation\Collections\Map\Map;
use Avax\DataFoundation\Internal\Conversion\JsonDecoding;

/**
 * JSON decoding boundary.
 */
final readonly class FromJson
{
    public static function toCollection(string $json) : Collection
    {
        return new Collection(items: self::toArray(json: $json));
    }

    /**
     * @return array<mixed>
     */
    public static function toArray(string $json) : array
    {
        $decoded = JsonDecoding::decode(json: $json, associative: true);

        return is_array($decoded) ? $decoded : [$decoded];
    }

    public static function toMap(string $json) : Map
    {
        return new Map(items: self::toArray(json: $json));
    }
}
