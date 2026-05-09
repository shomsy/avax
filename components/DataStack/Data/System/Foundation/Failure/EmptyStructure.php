<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

final class EmptyStructure extends DataFailure
{
    public static function forOperation(string $operation) : self
    {
        return new self(message: "Cannot {$operation} on an empty structure.");
    }
}
