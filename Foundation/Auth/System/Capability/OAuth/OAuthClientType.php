<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

enum OAuthClientType: string
{
    case PUBLIC       = 'public';
    case CONFIDENTIAL = 'confidential';
}
