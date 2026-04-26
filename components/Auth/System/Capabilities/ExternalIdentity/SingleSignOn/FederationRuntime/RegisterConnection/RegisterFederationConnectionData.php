<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection;

use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationProvider;

final readonly class RegisterFederationConnectionData
{
    /** @var array<string, list<string>> */
    public array $groupRoleMap;
    public bool  $ssoOnly;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string             $tenantSlug,
        public string             $name,
        public FederationProvider $provider,
        public string             $domain,
        bool|null                 $ssoOnly = null,
        array|null                $groupRoleMap = null,
        public string|null        $metadataUrl = null,
        public bool               $breakGlassAllowed = false
    )
    {
        $ssoOnly            ??= false;
        $groupRoleMap       ??= [];
        $this->ssoOnly      = $ssoOnly;
        $this->groupRoleMap = $groupRoleMap;
    }
}
