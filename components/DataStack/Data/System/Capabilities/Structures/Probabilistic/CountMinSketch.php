<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\ProbabilisticStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

/**
 * CountMinSketch — a probabilistic data structure for frequency estimation.
 *
 * Estimates never undercount. May overcount due to hash collisions.
 */
final readonly class CountMinSketch implements Countable, ProbabilisticStructure
{
    /**
     * @param array<int, array<int, int<0, max>>> $table
     */
    private function __construct(
        private array $table,
        private int $depth,
        private int $width,
        private int $total,
    ) {}

    public static function empty(int $depth = 5, int $width = 1000) : self
    {
        if ($depth < 1 || $width < 1) {
            throw InvalidCapacity::because(reason: 'CountMinSketch dimensions must be positive.');
        }

        return new self(
            table: array_fill(start_index: 0, count: $depth, value: array_fill(start_index: 0, count: $width, value: 0)),
            depth: $depth,
            width: $width,
            total: 0,
        );
    }

    public function add(mixed $value) : self
    {
        return $this->addWithCount(value: $value, count: 1);
    }

    public function addWithCount(mixed $value, int $count = 1) : self
    {
        if ($count < 1) {
            return $this;
        }

        $table = $this->table;
        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash(value: $value, seed: $i) % $this->width;
            $row = $table[$i];
            $row[$index] += $count;
            $table[$i] = $row;
        }

        return new self(table: $table, depth: $this->depth, width: $this->width, total: $this->total + $count);
    }

    /**
     * Probabilistic membership: if estimate > 0, the value was likely added.
     */
    public function mightContain(mixed $value) : bool
    {
        return $this->estimate(value: $value) > 0;
    }

    public function estimate(mixed $value) : int
    {
        $min = PHP_INT_MAX;
        for ($i = 0; $i < $this->depth; $i++) {
            $index = $this->hash(value: $value, seed: $i) % $this->width;
            $min = min($min, $this->table[$i][$index]);
        }

        return $min;
    }

    /**
     * @return array{depth: int, width: int, total: int}
     */
    public function summary() : array
    {
        return ['depth' => $this->depth, 'width' => $this->width, 'total' => $this->total];
    }

    private function hash(mixed $value, int $seed) : int
    {
        $serialized = is_scalar(value: $value) || $value === null ? (string) $value : serialize(value: $value);

        return crc32(string: $seed . ':' . $serialized) & 0x7FFFFFFF;
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->total === 0;
    }

    #[Override]
    public function count() : int
    {
        return max(0, $this->total);
    }
}
