<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\PublicSurface;

use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayReader;
use Avax\Components\DataStack\Data\System\Capabilities\Arrays\ArrayWriter;
use Avax\Components\DataStack\Data\System\Capabilities\Collection\Collection;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\AverageValues;
use Avax\Components\DataStack\Data\System\Flows\Aggregate\SumValues;
use Avax\Components\DataStack\Data\System\Flows\Read\ReadNestedValue;
use Avax\Components\DataStack\Data\System\Flows\Write\WriteNestedValue;
use Override;

/**
 * Data PublicSurface.
 */
final readonly class Data implements DataInterface
{
    public function __construct(
        private ArrayReader $arrayReader,
        private ArrayWriter $arrayWriter,
        private ReadNestedValue $readNestedValue,
        private WriteNestedValue $writeNestedValue,
        private SumValues $sumValues,
        private AverageValues $averageValues,
    ) {
    }

    public function get(array $data, string $key, mixed $default = null): mixed
    {
        return $this->readNestedValue->execute($data, $key, $default);
    }

    public function set(array &$data, string $key, mixed $value): void
    {
        $this->writeNestedValue->execute($data, $key, $value);
    }

    public function sum(iterable $items, string|callable|null $key = null): int|float
    {
        return $this->sumValues->execute($items, $key);
    }

    public function avg(iterable $items, string|callable|null $key = null): float
    {
        return $this->averageValues->execute($items, $key);
    }

    #[Override]
    public function array(): ArrayReader
    {
        return $this->arrayReader;
    }

    #[Override]
    public function write(): ArrayWriter
    {
        return $this->arrayWriter;
    }

    #[Override]
    /**
     * @param array<array-key, mixed> $items
     * @return Collection
     */
    public function collect(array $items = []): Collection
    {
        return Collection::make(items: $items);
    }
}
