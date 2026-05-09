<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Hashing;

use InvalidArgumentException;

/**
 * ObjectHash — hash function based on object identity (spl_object_id).
 *
 * Two distinct objects always produce different hashes even if their state is identical.
 * The same object instance always produces the same hash within a process.
 */
final readonly class ObjectHash implements HashFunction
{
    public function hash(mixed $value) : string
    {
        if (! is_object(value: $value)) {
            throw new InvalidArgumentException(message: 'ObjectHash requires an object.');
        }

        return 'object:' . spl_object_id(object: $value);
    }
}
