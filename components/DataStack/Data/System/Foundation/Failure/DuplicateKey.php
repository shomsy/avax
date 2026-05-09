<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

final class DuplicateKey extends DataFailure
{
    public static function named(int|string $key) : self
    {
        return new self(message: "Duplicate key [{$key}] is not allowed.");
    }
}
