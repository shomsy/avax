<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

final class InvalidStructureOperation extends DataFailure
{
    public static function because(string $reason) : self
    {
        return new self(message: $reason);
    }
}
