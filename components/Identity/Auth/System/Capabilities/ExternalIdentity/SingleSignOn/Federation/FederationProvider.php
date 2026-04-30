<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

enum FederationProvider: string
{
    case OIDC = 'oidc';
    case SAML = 'saml';
}
