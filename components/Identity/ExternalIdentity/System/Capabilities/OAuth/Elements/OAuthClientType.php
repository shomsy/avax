<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

enum OAuthClientType: string
{
    case PUBLIC = 'public';
    case CONFIDENTIAL = 'confidential';
}
