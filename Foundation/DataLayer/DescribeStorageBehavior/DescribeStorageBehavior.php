<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

/**
 * DescribeStorageBehavior - groups storage engine assumption descriptions without implementing a database engine.
 */
final readonly class DescribeStorageBehavior
{
    public function describeResponsibility() : string
    {
        return 'groups storage engine assumption descriptions without implementing a database engine.';
    }
}
