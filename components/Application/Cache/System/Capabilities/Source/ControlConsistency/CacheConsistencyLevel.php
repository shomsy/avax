<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

enum CacheConsistencyLevel: string
{
    case STRONG = 'strong';
    case EVENTUAL = 'eventual';
    case LOCAL = 'local';
    case READ_YOUR_WRITES = 'read_your_writes';
}
