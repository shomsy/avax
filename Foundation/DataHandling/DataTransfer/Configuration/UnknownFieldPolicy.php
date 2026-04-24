<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransfer\Configuration;

enum UnknownFieldPolicy: string
{
    case Reject  = 'reject';
    case Ignore  = 'ignore';
    case Collect = 'collect';
}
