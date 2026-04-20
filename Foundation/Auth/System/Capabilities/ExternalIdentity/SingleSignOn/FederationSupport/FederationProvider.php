<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Federation;

enum FederationProvider: string
{
    case OIDC = 'oidc';
    case SAML = 'saml';
}
