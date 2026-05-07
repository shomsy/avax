<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Foundation;

/**
 * Interface for ID generation.
 */
interface IdGeneratorInterface
{
    /**
     * Generate a new unique ID.
     */
    public function generate() : int;
}
