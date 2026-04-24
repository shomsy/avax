<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when a structure cannot preserve its invariant.
 */
final class InvalidStructureException extends DataFoundationException
{
    public static function capacityMustBePositive(int $capacity) : self
    {
        return new self(message: "Capacity must be greater than zero, got '{$capacity}'.");
    }

    public static function treeNodeCannotReferenceItself() : self
    {
        return new self(message: 'A tree node cannot contain itself as a child.');
    }
}
