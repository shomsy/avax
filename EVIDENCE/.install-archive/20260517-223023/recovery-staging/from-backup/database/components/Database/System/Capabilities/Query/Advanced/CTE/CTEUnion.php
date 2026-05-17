<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\CTE;

final class CTEUnion
{
    public function __construct(
        public readonly string $left,
        public readonly string $right,
        public readonly bool $all = true,
    ) {
    }

    public function toSql(): string
    {
        return $this->left.($this->all ? ' UNION ALL ' : ' UNION ').$this->right;
    }
}
