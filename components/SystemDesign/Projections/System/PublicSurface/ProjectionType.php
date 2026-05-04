<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Projections\System\PublicSurface;

enum ProjectionType: string
{
    case READ_MODEL = 'read-model';
    case MATERIALIZED_VIEW = 'materialized-view';
    case AGGREGATE = 'aggregate';
    case COUNTER = 'counter';
}
