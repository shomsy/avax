<?php

declare(strict_types=1);

namespace Avax\DataLayer\ShapeStoredData;

/**
 * DescribeStoredIndex - describes index intent before a migration executes it.
 */
final readonly class DescribeStoredIndex
{
    public function describeResponsibility() : string
    {
        return 'describes index intent before a migration executes it.';
    }
}
