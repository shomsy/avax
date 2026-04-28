<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

enum OAuthClientType: string
{
    case PUBLIC       = 'public';
    case CONFIDENTIAL = 'confidential';
}
