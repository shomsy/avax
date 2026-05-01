<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

enum ReplicationPolicy: string
{
    case SYNCHRONOUS  = 'synchronous';
    case ASYNCHRONOUS = 'asynchronous';
    case QUORUM       = 'quorum';
}
