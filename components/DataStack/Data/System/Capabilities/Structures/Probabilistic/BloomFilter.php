<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\Probabilistic;

use Avax\Components\DataStack\Data\System\Capabilities\Structures\Foundation\ProbabilisticStructure;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage\BitStringStorage;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

final readonly class BloomFilter implements Countable, ProbabilisticStructure
{
    public function __construct(private BitStringStorage $bits, private int $hashCount = 3, private int $insertions = 0)
    {
        if ($hashCount < 1) {
            throw InvalidCapacity::because(reason: 'Bloom filter hash count must be greater than zero.');
        }

        if ($bits->count() < 1) {
            throw InvalidCapacity::because(reason: 'Bloom filter bit size must be greater than zero.');
        }
    }

    #[Override]
    public function count() : int
    {
        return max(0, $this->insertions);
    }

    public static function empty(int $bits = 128, int $hashCount = 3) : self
    {
        return new self(bits: BitStringStorage::withSize(size: $bits), hashCount: $hashCount);
    }

    #[Override]
    public function add(mixed $value) : self
    {
        $bits = $this->bits;

        foreach ($this->indexesFor(value: $value) as $index) {
            $bits = $bits->set(index: $index);
        }

        return new self(bits: $bits, hashCount: $this->hashCount, insertions: $this->insertions + 1);
    }

    /**
     * @return list<int>
     */
    private function indexesFor(mixed $value) : array
    {
        $serialized = is_scalar(value: $value) || $value === null ? (string) $value : serialize(value: $value);
        $indexes    = [];

        for ($i = 0; $i < $this->hashCount; $i++) {
            $hash      = crc32(string: $i . ':' . $serialized);
            $indexes[] = (($hash & 0x7FFFFFFF) % $this->bits->count());
        }

        return $indexes;
    }

    #[Override]
    public function mightContain(mixed $value) : bool
    {
        foreach ($this->indexesFor(value: $value) as $index) {
            if (! $this->bits->isSet(index: $index)) {
                return false;
            }
        }

        return true;
    }

    public function bitString() : string
    {
        return $this->bits->bits();
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->insertions === 0;
    }
}
