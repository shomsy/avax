<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Advanced\CTE;

final readonly class CTEUnion
{
    public function __construct(
        public string $left,
        public string $right,
        public bool $all = true,
    ) {
    }

    public function toSql(): string
    {
        return $this->left.($this->all ? ' UNION ALL ' : ' UNION ').$this->right;
    }
}
