<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class PartitionBuilder
{
    /** @var list<string> */
    private array $columns = [];

    public function __construct(private readonly GrammarInterface $grammar) {}

    public function by(string ...$columns) : self
    {
        $this->columns = $columns;

        return $this;
    }

    public function toSql() : string
    {
        if ($this->columns === []) {
            return '';
        }

        return 'PARTITION BY ' . implode(
                separator: ', ',
                array    : array_map(callback: fn ($column) : string => $this->grammar->wrap(value: $column), array: $this->columns),
            );
    }
}
