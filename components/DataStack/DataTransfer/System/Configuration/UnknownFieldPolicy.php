<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\Configuration;

enum UnknownFieldPolicy: string
{
    case Reject  = 'reject';
    case Ignore  = 'ignore';
    case Collect = 'collect';
}
