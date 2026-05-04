<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Consistency\System\PublicSurface;

enum ConsistencyLevel: string
{
    case EVENTUAL = 'eventual';
    case CAUSAL = 'causal';
    case READ_YOUR_WRITES = 'read-your-writes';
    case SEQUENTIAL = 'sequential';
    case LINEARIZABLE = 'linearizable';
}
