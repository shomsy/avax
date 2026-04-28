<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

enum PkceMethod: string
{
    case S256 = 'S256';
}
