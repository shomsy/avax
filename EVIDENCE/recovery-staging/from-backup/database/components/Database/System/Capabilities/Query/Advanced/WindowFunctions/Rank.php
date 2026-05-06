<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Advanced\WindowFunctions;

use Avax\Database\System\Capabilities\Query\Grammar\GrammarInterface;

final class Rank
{
    public static function build(GrammarInterface $grammar): WindowBuilder
    {
        return WindowBuilder::rank(grammar: $grammar);
    }

    public static function dense(GrammarInterface $grammar): WindowBuilder
    {
        return WindowBuilder::denseRank(grammar: $grammar);
    }
}
