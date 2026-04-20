<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

enum FederationProvider: string
{
    case OIDC = 'oidc';
    case SAML = 'saml';
}
