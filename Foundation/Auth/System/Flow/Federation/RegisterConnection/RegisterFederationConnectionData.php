<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\RegisterConnection;

use Avax\Auth\System\Capability\Federation\FederationProvider;

final readonly class RegisterFederationConnectionData
{
    public bool               $breakGlassAllowed;
    public string|null        $metadataUrl;
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
