<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\ProbabilisticStructure;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;

/**
 * HyperLogLog — a probabilistic cardinality estimator.
 *
 * Estimates the number of distinct elements in a set with very low memory usage.
 */
final readonly class HyperLogLog implements Countable, ProbabilisticStructure
{
    private const float ALPHA = 0.7213 / (1.0 + 1.079);

    /**
     * @param array<int, int<0, max>> $registers
     */
    private function __construct(
        private array $registers,
        private int $precision,
        private int $size,
    ) {}

    public static function empty(int $precision = 14) : self
    {
        if ($precision < 4 || $precision > 16) {
            throw InvalidCapacity::because(reason: 'HyperLogLog precision must be between 4 and 16.');
        }

        return new self(
            registers: array_fill(start_index: 0, count: 1 << $precision, value: 0),
            precision: $precision,
            size: 1 << $precision,
        );
    }

    /**
     * HyperLogLog does not support membership queries directly.
     * This returns false because HLL only estimates cardinality.
     */
    public function mightContain(mixed $value) : bool
    {
        return false;
    }

    public function add(mixed $value) : self
    {
        $hash = $this->hash(value: $value);
        $index = $hash & ($this->size - 1);
        $leadingZeros = $this->countLeadingZeros(value: $hash >> $this->precision);

        $registers = $this->registers;
        $registers[$index] = max($registers[$index], $leadingZeros + 1);

        return new self(registers: $registers, precision: $this->precision, size: $this->size);
    }

    public function count() : int
    {
        $sum = 0.0;
        $zeros = 0;

        foreach ($this->registers as $register) {
            $sum += 2.0 ** -$register;
            if ($register === 0) {
                $zeros++;
            }
        }

        $estimate = $this->alpha() * $this->size * $this->size / $sum;

        if ($estimate <= 2.5 * $this->size && $zeros > 0) {
            $estimate = $this->size * log(num: $this->size / $zeros);
        }

        return max(0, (int) round(num: $estimate));
    }

    private function hash(mixed $value) : int
    {
        $serialized = is_scalar(value: $value) || $value === null ? (string) $value : json_encode(value: $value, flags: JSON_THROW_ON_ERROR);

        return crc32(string: $serialized) & 0xFFFFFFFF;
    }

    private function countLeadingZeros(int $value) : int
    {
        if ($value === 0) {
            return 32 - $this->precision + 1;
        }

        $count = 0;
        for ($i = 31; $i >= 0; $i--) {
            if (($value >> $i) & 1) {
                break;
            }
            $count++;
        }

        return $count;
    }

    private function alpha() : float
    {
        return match ($this->size) {
            16 => 0.673,
            32 => 0.697,
            64 => 0.709,
            default => self::ALPHA,
        };
    }

    public function isEmpty() : bool
    {
        return array_sum(array: $this->registers) === 0;
    }

    /**
     * @return array{precision: int, registers: array<int, int<0, max>>}
     */
    public function summary() : array
    {
        return ['precision' => $this->precision, 'registers' => $this->registers];
    }
}
