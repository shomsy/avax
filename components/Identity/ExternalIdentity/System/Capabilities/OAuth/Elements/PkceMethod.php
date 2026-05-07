<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

enum PkceMethod: string
{
    case S256 = 'S256';
}
