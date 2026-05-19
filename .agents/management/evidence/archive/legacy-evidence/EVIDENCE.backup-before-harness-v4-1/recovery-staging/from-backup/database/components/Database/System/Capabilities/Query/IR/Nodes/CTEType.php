<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\IR\Nodes;

enum CTEType
{
    case SIMPLE;
    case RECURSIVE;
}
