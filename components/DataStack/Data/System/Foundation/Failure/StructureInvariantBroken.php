<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Failure;

final class StructureInvariantBroken extends DataFailure
{
    public static function named(string $invariant) : self
    {
        return new self(message: "Structure invariant [{$invariant}] was broken.");
    }
}
