<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Hashing;

/**
 * HashFunction capability for producing value hashes.
 *
 * A hash function converts an arbitrary value into a stable hash string.
 */
interface HashFunction
{
    /**
     * Produce a stable hash for the given value.
     */
    public function hash(mixed $value) : string;
}
