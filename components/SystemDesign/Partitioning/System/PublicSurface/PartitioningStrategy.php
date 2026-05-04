<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Partitioning\System\PublicSurface;

enum PartitioningStrategy: string
{
    case RANGE = 'range';
    case HASH = 'hash';
    case LIST = 'list';
    case COMPOSITE = 'composite';
}
