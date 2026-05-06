<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

final readonly class ProjectionNode
{
    public function __construct(
        public string $className,
        public array $columnMap = [],
    ) {
    }
}
