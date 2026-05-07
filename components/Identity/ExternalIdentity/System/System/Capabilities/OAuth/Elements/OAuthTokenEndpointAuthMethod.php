<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OAuth\Elements;

enum OAuthTokenEndpointAuthMethod: string
{
    case NONE                = 'none';
    case CLIENT_SECRET_BASIC = 'client_secret_basic';
    case CLIENT_SECRET_POST  = 'client_secret_post';
}
