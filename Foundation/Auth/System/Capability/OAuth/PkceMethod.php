<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

enum PkceMethod: string
{
    case S256 = 'S256';
}
