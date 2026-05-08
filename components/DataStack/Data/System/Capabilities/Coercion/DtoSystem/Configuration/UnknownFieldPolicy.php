<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Coercion\DtoSystem\Configuration;

enum UnknownFieldPolicy: string
{
    case Reject = 'reject';
    case Ignore = 'ignore';
    case Collect = 'collect';
}
