<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Replication\System\PublicSurface;

enum ReplicationStrategy: string
{
    case SINGLE_LEADER = 'single-leader';
    case MULTI_LEADER = 'multi-leader';
    case LEADERSLESS = 'leaderless';
}
