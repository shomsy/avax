<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\IR\Nodes;

final class ProjectionNode
{
    public function __construct(
        public readonly string $className,
        public readonly array  $columnMap = [],
    ) {}
}
