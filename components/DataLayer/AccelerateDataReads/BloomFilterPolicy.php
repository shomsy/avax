<?php

declare(strict_types=1);

namespace components\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

enum BloomFilterPolicyType: string
{
    case KEYS     = 'keys';
    case ROWS     = 'rows';
    case COMPOUND = 'compound';
}

final readonly class BloomFilterPolicy
{
    public function __construct(
        public BloomFilterPolicyType $type,
        public float                 $falsePositiveRate,
        public int                   $expectedKeys,
        public bool                  $autoCreate
    )
    {
        if ($this->falsePositiveRate <= 0 || $this->falsePositiveRate >= 1) {
            throw new InvalidArgumentException(message: 'False positive rate must be between 0 and 1.');
        }
    }

    public static function keys(float $fpr = 0.01) : self
    {
        return new self(type: BloomFilterPolicyType::KEYS, falsePositiveRate: $fpr, expectedKeys: 1000000, autoCreate: true);
    }

    public static function rows(float $fpr = 0.05) : self
    {
        return new self(type: BloomFilterPolicyType::ROWS, falsePositiveRate: $fpr, expectedKeys: 10000000, autoCreate: true);
    }

    public function describeResponsibility() : string
    {
        return 'records bloom filter purpose and false-positive tolerance.';
    }

    public function toMetadata() : array
    {
        return [
            'type'                => $this->type->value,
            'false_positive_rate' => $this->falsePositiveRate,
            'expected_keys'       => $this->expectedKeys,
            'auto_create'         => $this->autoCreate,
            'optimal_bits'        => $this->calculateOptimalBits(),
            'hash_count'          => $this->calculateHashCount(),
        ];
    }

    public function calculateOptimalBits() : int
    {
        return (int) ceil(-($this->expectedKeys * log($this->falsePositiveRate)) / (log(2) ** 2));
    }

    public function calculateHashCount() : int
    {
        $bits = $this->calculateOptimalBits();

        return (int) ceil($bits / $this->expectedKeys * log(2));
    }
}