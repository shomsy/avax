<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

use InvalidArgumentException;

use function count;

/**
 * Bloom filter implementation for query optimization.
 *
 * A probabilistic data structure that tests whether an element is a member
 * of a set. False positives are possible, but false negatives are not.
 *
 * Use cases:
 * - Pre-filtering queries (avoid DB lookups for definitely-missing keys)
 * - Cache miss prevention
 * - Deduplication of seen values
 *
 * @see https://en.wikipedia.org/wiki/Bloom_filter
 */
final class BloomFilter
{
    /**
     * @var list<bool> The bit array
     */
    private array $bits;

    /**
     * @var int Number of bits in the array
     */
    private readonly int $bitCount;

    /**
     * @var int Number of hash functions to apply
     */
    private readonly int $hashCount;

    /**
     * @var int Number of items added to the filter
     */
    private int $itemCount = 0;

    /**
     * @var float Expected false positive rate (0.0 to 1.0)
     */
    private readonly float $falsePositiveRate;

    /**
     * @var int Expected number of items to be stored
     */
    private readonly int $expectedItems;

    /**
     * Private constructor. Use factory methods to create instances.
     */
    private function __construct(
        int $bitCount,
        int $hashCount,
        float $falsePositiveRate,
        int $expectedItems,
    ) {
        $this->bitCount = $bitCount;
        $this->hashCount = $hashCount;
        $this->falsePositiveRate = $falsePositiveRate;
        $this->expectedItems = $expectedItems;
        $this->bits = array_fill(0, $bitCount, false);
    }

    /**
     * Creates a Bloom filter with optimal bit array size and hash count
     * based on the expected number of items and desired false positive rate.
     *
     * @param  int  $expectedItems  Expected number of items to store
     * @param  float  $falsePositiveRate  Desired false positive rate (0.0 to 1.0, exclusive)
     */
    public static function create(int $expectedItems, float $falsePositiveRate = 0.01): self
    {
        if ($expectedItems <= 0) {
            throw new InvalidArgumentException('Expected items must be greater than zero');
        }

        if ($falsePositiveRate <= 0.0 || $falsePositiveRate >= 1.0) {
            throw new InvalidArgumentException('False positive rate must be between 0 and 1 (exclusive)');
        }

        // Calculate optimal bit array size: m = -n * ln(p) / (ln(2))^2
        $bitCount = (int) ceil(-$expectedItems * log($falsePositiveRate) / (log(2) ** 2));

        // Calculate optimal number of hash functions: k = (m/n) * ln(2)
        $hashCount = (int) ceil(($bitCount / $expectedItems) * log(2));

        return new self($bitCount, $hashCount, $falsePositiveRate, $expectedItems);
    }

    /**
     * Creates a Bloom filter with explicit size and hash count.
     *
     * @param  int  $bitCount  Number of bits in the array
     * @param  int  $hashCount  Number of hash functions
     */
    public static function withSize(int $bitCount, int $hashCount): self
    {
        if ($bitCount <= 0) {
            throw new InvalidArgumentException('Bit count must be greater than zero');
        }

        if ($hashCount <= 0) {
            throw new InvalidArgumentException('Hash count must be greater than zero');
        }

        return new self($bitCount, $hashCount, 0.01, 1000);
    }

    /**
     * Adds an item to the bloom filter.
     *
     * @param  string  $item  The item to add
     */
    public function add(string $item): void
    {
        foreach ($this->getHashIndices($item) as $index) {
            $this->bits[$index] = true;
        }

        $this->itemCount++;
    }

    /**
     * Calculates hash indices for an item using multiple hash functions.
     *
     * Uses double hashing technique: h(i, x) = (h1(x) + i * h2(x)) mod m
     * This generates k different hash values from just 2 hash functions.
     *
     * @return list<int>
     */
    private function getHashIndices(string $item): array
    {
        $indices = [];
        $h1 = $this->hash1($item);
        $h2 = $this->hash2($item);

        for ($i = 0; $i < $this->hashCount; $i++) {
            $indices[] = ($h1 + $i * $h2) % $this->bitCount;
        }

        return $indices;
    }

    /**
     * First hash function (MurmurHash-inspired).
     */
    private function hash1(string $item): int
    {
        $hash = crc32($item);

        return $hash % $this->bitCount;
    }

    /**
     * Second hash function (different seed).
     */
    private function hash2(string $item): int
    {
        $hash = crc32(strrev($item));

        // Ensure non-zero result for double hashing
        return ($hash % ($this->bitCount - 1)) + 1;
    }

    /**
     * Checks if an item is definitely not in the set.
     *
     * This is the inverse of mightContain.
     */
    public function definitelyNotContains(string $item): bool
    {
        return ! $this->mightContain($item);
    }

    /**
     * Checks if an item might be in the set.
     *
     * Returns true if the item is PROBABLY in the set (possible false positive).
     * Returns false if the item is DEFINITELY NOT in the set (no false negatives).
     *
     * @param  string  $item  The item to check
     */
    public function mightContain(string $item): bool
    {
        foreach ($this->getHashIndices($item) as $index) {
            if (! $this->bits[$index]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the total number of bits in the array.
     */
    public function getBitCount(): int
    {
        return $this->bitCount;
    }

    /**
     * Returns the current number of set bits.
     */
    public function setBitCount(): int
    {
        return count(array_filter($this->bits));
    }

    /**
     * Returns the number of hash functions used.
     */
    public function getHashCount(): int
    {
        return $this->hashCount;
    }

    /**
     * Returns the number of items added to the filter.
     */
    public function getItemCount(): int
    {
        return $this->itemCount;
    }

    /**
     * Returns the estimated false positive rate based on current fill level.
     *
     * Formula: (1 - e^(-kn/m))^k
     */
    public function estimatedFalsePositiveRate(): float
    {
        if ($this->itemCount === 0) {
            return 0.0;
        }

        $ratio = exp(-$this->hashCount * $this->itemCount / $this->bitCount);

        return (1.0 - $ratio) ** $this->hashCount;
    }

    /**
     * Returns whether the filter is considered saturated
     * (more than 50% of bits are set, making false positives very likely).
     */
    public function isSaturated(): bool
    {
        return $this->fillRatio() > 0.5;
    }

    /**
     * Returns the fill ratio (percentage of bits that are set).
     */
    public function fillRatio(): float
    {
        if ($this->bitCount === 0) {
            return 0.0;
        }

        return $this->setBitCount() / $this->bitCount;
    }

    /**
     * Resets the filter, clearing all bits.
     */
    public function reset(): void
    {
        $this->bits = array_fill(0, $this->bitCount, false);
        $this->itemCount = 0;
    }

    /**
     * Merges another bloom filter into this one.
     *
     * Both filters must have the same size and hash count.
     *
     * @throws InvalidArgumentException If filters are incompatible
     */
    public function merge(self $other): void
    {
        if ($this->bitCount !== $other->bitCount) {
            throw new InvalidArgumentException('Cannot merge bloom filters with different bit counts');
        }

        if ($this->hashCount !== $other->hashCount) {
            throw new InvalidArgumentException('Cannot merge bloom filters with different hash counts');
        }

        for ($i = 0; $i < $this->bitCount; $i++) {
            $this->bits[$i] = $this->bits[$i] || $other->bits[$i];
        }

        $this->itemCount += $other->itemCount;
    }

    /**
     * Returns the bits array (for serialization/debugging).
     *
     * @return list<bool>
     */
    public function getBits(): array
    {
        return $this->bits;
    }

    /**
     * Returns the expected number of items this filter was configured for.
     */
    public function getExpectedItems(): int
    {
        return $this->expectedItems;
    }

    /**
     * Returns the configured false positive rate.
     */
    public function getFalsePositiveRate(): float
    {
        return $this->falsePositiveRate;
    }
}
