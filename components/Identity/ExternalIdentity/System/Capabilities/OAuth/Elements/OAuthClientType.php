<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support;

enum OAuthClientType: string
{
    case PUBLIC       = 'public';
    case CONFIDENTIAL = 'confidential';
}
