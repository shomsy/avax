<?php

declare(strict_types=1);

namespace Avax\DataFoundation\DataTransfer\Configuration;

enum UnknownFieldPolicy: string
{
    case Reject  = 'reject';
    case Ignore  = 'ignore';
    case Collect = 'collect';
}
