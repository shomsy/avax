<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\PublicSurface;

use Avax\Components\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\Data\System\Capabilities\Collections\Collection;
use Avax\Components\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\Data\System\Flows\Write\WriteNestedValue;

/**
 * Data PublicSurface.
 *
 * Orchestrates simple array capabilities and powerful nested/aggregation flows.
 * Restored from avax-backup.txt and restructured per refactor.md.
 */
final readonly class Data implements DataInterface
{
    public function __construct(
        private ArrayReader $arrayReader,
        private ArrayWriter $arrayWriter,
        private ReadNestedValue  $readNestedValue,
        private WriteNestedValue $writeNestedValue,
        private SumValues        $sumValues,
        private AverageValues    $averageValues,
    ) {
    }

    public function get(array $data, string $key, mixed $default = null) : mixed
    {
        return $this->readNestedValue->execute($data, $key, $default);
    }

    public function set(array &$data, string $key, mixed $value) : void
    {
        $this->writeNestedValue->execute($data, $key, $value);
    }

    public function sum(iterable $items, string|callable|null $key = null) : int|float
    {
        return $this->sumValues->execute($items, $key);
    }

    public function avg(iterable $items, string|callable|null $key = null) : float
    {
        return $this->averageValues->execute($items, $key);
    }

    public function array(): ArrayReader
    {
        return $this->arrayReader;
    }

    public function write(): ArrayWriter
    {
        return $this->arrayWriter;
    }

    public function collect(array $items = []) : Collection
    {
        return Collection::from(items: $items);
    }
}