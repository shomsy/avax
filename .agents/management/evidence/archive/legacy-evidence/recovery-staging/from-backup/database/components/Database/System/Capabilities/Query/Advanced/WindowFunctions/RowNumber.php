<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use components\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class RowNumber
{
    public static function build(GrammarInterface $grammar): WindowBuilder
    {
        return WindowBuilder::rowNumber(grammar: $grammar);
    }
}
