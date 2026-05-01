<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support;

enum PkceMethod: string
{
    case S256 = 'S256';
}
