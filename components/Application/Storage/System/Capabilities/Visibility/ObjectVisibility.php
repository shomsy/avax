<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Capabilities\Visibility;

enum ObjectVisibility: string
{
    case PUBLIC  = 'public';
    case PRIVATE = 'private';
}