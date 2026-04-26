<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

final readonly class DescribeBloomFilter
{
    public function __construct(
        public string $name,
        public string $targetTable,
        public array  $columns,
        public int    $sizeBits,
        public int    $hashFunctions,
        public float  $falsePositiveRate
    ) {}

    public static function forTable(string $table, array $columns, int|null $expectedKeys = null, float $fpr = 0.01) : self
    {
        $expectedKeys  ??= 1000000;
        $sizeBits      = (int) ceil(-($expectedKeys * log($fpr)) / (log(2) ** 2));
        $hashFunctions = (int) ceil(($sizeBits / $expectedKeys) * log(2));
        $name          = "bloom_{$table}_" . implode('_', $columns);

        return new self(
            name             : $name,
            targetTable      : $table,
            columns          : $columns,
            sizeBits         : $sizeBits,
            hashFunctions    : $hashFunctions,
            falsePositiveRate: $fpr
        );
    }

    public function describeResponsibility() : string
    {
        return 'describes bloom filter including name, target table, columns, size, and FPR.';
    }

    public function toMetadata() : array
    {
        return [
            'name'                => $this->name,
            'target_table'        => $this->targetTable,
            'columns'             => $this->columns,
            'size_bits'           => $this->sizeBits,
            'hash_functions'      => $this->hashFunctions,
            'false_positive_rate' => $this->falsePositiveRate,
            'size_bytes'          => $this->sizeBytes(),
        ];
    }

    public function sizeBytes() : int
    {
        return (int) ceil($this->sizeBits / 8);
    }
}