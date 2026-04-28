<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\Advanced\WindowFunctions;

enum WindowFunction: string
{
    case ROW_NUMBER  = 'ROW_NUMBER()';
    case RANK        = 'RANK()';
    case DENSE_RANK  = 'DENSE_RANK()';
    case NTILE       = 'NTILE';
    case LAG         = 'LAG';
    case LEAD        = 'LEAD';
    case FIRST_VALUE = 'FIRST_VALUE';
    case LAST_VALUE  = 'LAST_VALUE';
    case NTH_VALUE   = 'NTH_VALUE';
    case COUNT       = 'COUNT';
    case SUM         = 'SUM';
    case AVG         = 'AVG';
    case MIN         = 'MIN';
    case MAX         = 'MAX';
}
