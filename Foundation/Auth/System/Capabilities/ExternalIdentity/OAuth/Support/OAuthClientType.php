<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

enum OAuthClientType: string
{
    case PUBLIC       = 'public';
    case CONFIDENTIAL = 'confidential';
}
