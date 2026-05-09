<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Structures\StructureStorage;

use Avax\Components\DataStack\Data\System\Foundation\Failure\IndexOutOfBounds;
use Avax\Components\DataStack\Data\System\Foundation\Failure\InvalidCapacity;
use Countable;
use Override;

final readonly class BitStringStorage implements Countable, StructureStorage
{
    public function __construct(private string $bits = '')
    {
        if (strspn(string: $bits, characters: '01') !== strlen(string: $bits)) {
            throw InvalidCapacity::because(reason: 'Bit string storage accepts only 0 and 1 characters.');
        }
    }

    public static function withSize(int $size) : self
    {
        if ($size < 0) {
            throw InvalidCapacity::because(reason: 'Bit string size cannot be negative.');
        }

        return new self(bits: str_repeat(string: '0', times: $size));
    }

    public function bits() : string
    {
        return $this->bits;
    }

    public function set(int $index) : self
    {
        $this->ensureIndexExists(index: $index);

        $bits         = $this->bits;
        $bits[$index] = '1';

        return new self(bits: $bits);
    }

    private function ensureIndexExists(int $index) : void
    {
        if ($index < 0 || $index >= strlen(string: $this->bits)) {
            throw IndexOutOfBounds::at(index: $index);
        }
    }

    public function clear(int $index) : self
    {
        $this->ensureIndexExists(index: $index);

        $bits         = $this->bits;
        $bits[$index] = '0';

        return new self(bits: $bits);
    }

    public function isSet(int $index) : bool
    {
        $this->ensureIndexExists(index: $index);

        return $this->bits[$index] === '1';
    }

    #[Override]
    public function count() : int
    {
        return strlen(string: $this->bits);
    }

    #[Override]
    public function isEmpty() : bool
    {
        return $this->bits === '';
    }
}
