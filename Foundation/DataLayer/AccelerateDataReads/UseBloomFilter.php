<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

enum BloomFilterType: string
{
    case STANDARD    = 'standard';
    case SCALABLE    = 'scalable';
    case COUNTING    = 'counting';
    case PARTITIONED = 'partitioned';
}

final readonly class UseBloomFilter
{
    public function __construct(
        public BloomFilterType $type,
        public int             $expectedElements,
        public float           $falsePositiveRate,
        public bool            $enabled
    )
    {
        if ($this->expectedElements < 1) {
            throw new InvalidArgumentException(message: 'Expected elements must be at least 1.');
        }
        if ($this->falsePositiveRate <= 0 || $this->falsePositiveRate >= 1) {
            throw new InvalidArgumentException(message: 'False positive rate must be between 0 and 1.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'configures bloom filter parameters including type, expected elements, and FPR.';
    }

    public static function standard(int|null $expectedElements = null, float $fpr = 0.01) : self
    {
        $expectedElements ??= 1000000;

        return new self(type: BloomFilterType::STANDARD, expectedElements: $expectedElements, falsePositiveRate: $fpr, enabled: true);
    }

    public static function scalable(int|null $expectedElements = null, float $fpr = 0.001) : self
    {
        $expectedElements ??= 10000000;

        return new self(type: BloomFilterType::SCALABLE, expectedElements: $expectedElements, falsePositiveRate: $fpr, enabled: true);
    }

    public function calculateOptimalSize() : int
    {
        $m = -($this->expectedElements * log($this->falsePositiveRate)) / (log(2) ** 2);

        return (int) ceil($m);
    }

    public function calculateOptimalHashes() : int
    {
        $m = $this->calculateOptimalSize();
        $n = $this->expectedElements;

        return (int) ceil(($m / $n) * log(2));
    }

    public function willUse() : bool
    {
        return $this->enabled && $this->expectedElements > 0;
    }

    public function plan(array $queryContext) : BloomFilterPlan
    {
        $willUse = $this->willUse();
        $size    = $willUse ? $this->calculateOptimalSize() : 0;
        $hashes  = $willUse ? $this->calculateOptimalHashes() : 0;

        return new BloomFilterPlan(
            willUse          : $willUse,
            type             : $this->type,
            sizeBits         : $size,
            hashFunctions    : $hashes,
            falsePositiveRate: $this->falsePositiveRate
        );
    }

    public function toMetadata() : array
    {
        return [
            'type'                => $this->type->value,
            'expected_elements'   => $this->expectedElements,
            'false_positive_rate' => $this->falsePositiveRate,
            'enabled'             => $this->enabled,
            'optimal_size_bits'   => $this->calculateOptimalSize(),
            'optimal_hashes'      => $this->calculateOptimalHashes(),
        ];
    }
}

final readonly class BloomFilterPlan
{
    public function __construct(
        public bool            $willUse,
        public BloomFilterType $type,
        public int             $sizeBits,
        public int             $hashFunctions,
        public float           $falsePositiveRate
    ) {}

    public function sizeBytes() : int
    {
        return (int) ceil($this->sizeBits / 8);
    }
}