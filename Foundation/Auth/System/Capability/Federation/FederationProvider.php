<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

enum FederationProvider: string
{
    case OIDC = 'oidc';
    case SAML = 'saml';
}
