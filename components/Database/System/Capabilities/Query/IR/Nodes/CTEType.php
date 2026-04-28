<?php

declare(strict_types=1);

namespace Avax\Components\Database\System\Capabilities\Query\IR\Nodes;

enum CTEType
{
    case SIMPLE;
    case RECURSIVE;
}
