<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity;

use Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\OAuth;
use Avax\Components\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\OpenIDConnect;
use Avax\Components\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\SingleSignOn;
use SensitiveParameter;

/**
 * External Identity capability coordinator.
 *
 * Exposes accessors for OAuth, OpenID Connect, and SSO (Federation) providers.
 */
final readonly class ExternalIdentity
{
    public function __construct(
        #[SensitiveParameter] private OAuth $oauth,
        private OpenIDConnect               $oidc,
        private SingleSignOn                $sso
    ) {}

    public function oauth() : OAuth
    {
        return $this->oauth;
    }

    public function oidc() : OpenIDConnect
    {
        return $this->oidc;
    }

    public function sso() : SingleSignOn
    {
        return $this->sso;
    }
}
