<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Values;

use InvalidArgumentException;

/**
 * Coordinate — an N-dimensional numeric coordinate.
 */
final readonly class Coordinate
{
    /** @var list<float|int> */
    private array $dimensions;

    public function __construct(float|int ...$values)
    {
        if ($values === []) {
            throw new InvalidArgumentException(message: 'Coordinate must have at least one dimension.');
        }
        $this->dimensions = array_values(array: $values);
    }

    public function dimensionality() : int
    {
        return count(value: $this->dimensions);
    }

    public function at(int $index) : float|int
    {
        return $this->dimensions[$index];
    }

    /**
     * @return list<float|int>
     */
    public function toArray() : array
    {
        return $this->dimensions;
    }

    public function distanceTo(self $other) : float
    {
        $sum   = 0.0;
        $count = min(count(value: $this->dimensions), count(value: $other->dimensions));
        for ($i = 0; $i < $count; $i++) {
            $diff = $this->dimensions[$i] - $other->dimensions[$i];
            $sum  += $diff * $diff;
        }

        return sqrt($sum);
    }
}
