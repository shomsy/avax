<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationSupport;

enum FederationProvider: string
{
    case OIDC = 'oidc';
    case SAML = 'saml';
}
