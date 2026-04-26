<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

enum PkceMethod: string
{
    case S256 = 'S256';
}
