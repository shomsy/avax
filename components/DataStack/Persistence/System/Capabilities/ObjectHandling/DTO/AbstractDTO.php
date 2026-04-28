<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ObjectHandling\DTO;

/**
 * Base class for all Data Transfer Objects.
 * Migrated from DataFoundation to Persistence per refactor.md.
 */
abstract readonly class AbstractDTO
{
    public function toArray() : array
    {
        return get_object_vars($this);
    }
}
