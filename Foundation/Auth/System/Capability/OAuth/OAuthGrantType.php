<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

/**
 * OAuth grant types explicitly supported by the package-owned client policy.
 */
enum OAuthGrantType: string
{
    case AUTHORIZATION_CODE = 'authorization_code';
    case REFRESH_TOKEN = 'refresh_token';
    case CLIENT_CREDENTIALS = 'client_credentials';
}
