<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

final class IndexOutOfBounds extends DataFailure
{
    public static function at(int $index) : self
    {
        return new self(message: "Index [{$index}] is outside the structure bounds.");
    }
}
