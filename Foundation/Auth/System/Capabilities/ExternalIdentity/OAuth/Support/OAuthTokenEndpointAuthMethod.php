<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth;

enum OAuthTokenEndpointAuthMethod: string
{
    case NONE                = 'none';
    case CLIENT_SECRET_BASIC = 'client_secret_basic';
    case CLIENT_SECRET_POST  = 'client_secret_post';
}
