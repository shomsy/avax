<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationRuntime\RegisterConnection;

use Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport\FederationProvider;

final readonly class RegisterFederationConnectionData
{
    public bool               $breakGlassAllowed;
    public string|null        $metadataUrl;
    /** @var array<string, list<string>> */
    public array              $groupRoleMap;
    public bool               $ssoOnly;
    public string             $domain;
    public FederationProvider $provider;
    public string             $name;
    public string             $tenantSlug;

    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        string             $tenantSlug,
        string             $name,
        FederationProvider $provider,
        string             $domain,
        bool|null          $ssoOnly = null,
        array|null         $groupRoleMap = null,
        string|null        $metadataUrl = null,
        bool               $breakGlassAllowed = false
    )
    {
        $ssoOnly                 ??= false;
        $groupRoleMap            ??= [];
        $this->tenantSlug        = $tenantSlug;
        $this->name              = $name;
        $this->provider          = $provider;
        $this->domain            = $domain;
        $this->ssoOnly           = $ssoOnly;
        $this->groupRoleMap      = $groupRoleMap;
        $this->metadataUrl       = $metadataUrl;
        $this->breakGlassAllowed = $breakGlassAllowed;
    }
}
