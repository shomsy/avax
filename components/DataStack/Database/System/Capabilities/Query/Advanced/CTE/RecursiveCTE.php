<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\CTE;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;

final readonly class RecursiveCTE
{
    public function __construct(
        public string $name,
        public QueryState $initial,
        public QueryState $recursive,
    ) {}

    public function register(CTEBuilder $cteBuilder): CTEBuilder
    {
        return $cteBuilder->withRecursive(name: $this->name, initialQuery: $this->initial, recursiveQuery: $this->recursive);
    }
}
