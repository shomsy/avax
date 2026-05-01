<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final readonly class CTENode
{
    public function __construct(
        public string $name,
        public array $columns,
        public string $query,
        public CTEType $cteType = CTEType::SIMPLE,
    ) {}

    public function getSql(GrammarInterface $grammar): string
    {
        $columns = $this->columns === []
            ? ''
            : '('.implode(separator: ', ', array: array_map(
                callback: static fn ($col): string => $grammar->wrap(value: $col),
                array   : $this->columns,
            )).')';

        return sprintf('%s%s AS (%s)', $this->name, $columns, $this->query);
    }
}
