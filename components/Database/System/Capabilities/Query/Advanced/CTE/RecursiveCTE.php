<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\Advanced\CTE;

use Avax\Components\Database\System\Capabilities\Query\State\QueryState;

final class RecursiveCTE
{
    public function __construct(
        public readonly string     $name,
        public readonly QueryState $initial,
        public readonly QueryState $recursive,
    ) {}

    public function register(CTEBuilder $builder) : CTEBuilder
    {
        return $builder->withRecursive(name: $this->name, initialQuery: $this->initial, recursiveQuery: $this->recursive);
    }
}
