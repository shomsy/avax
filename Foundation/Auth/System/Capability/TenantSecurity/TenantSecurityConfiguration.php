<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantSecurity;

final readonly class TenantSecurityConfiguration
{
    public int         $rolloutVersion;
    public string      $policyProfile;
    public array       $groupRoleMap;
    public array       $verifiedDomains;
    public string|null $scimDirectoryId;
    public string|null $federationConnectionId;
    public string      $tenantSlug;

    /**
     * @param list<string>                $verifiedDomains
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        string      $tenantSlug,
        string|null $federationConnectionId = null,
        string|null $scimDirectoryId = null,
        array|null  $verifiedDomains = null,
        array|null  $groupRoleMap = null,
        string|null $policyProfile = null,
        int         $rolloutVersion = 1
    )
    {
        $verifiedDomains              ??= [];
        $groupRoleMap                 ??= [];
        $policyProfile                ??= 'user';
        $this->tenantSlug             = $tenantSlug;
        $this->federationConnectionId = $federationConnectionId;
        $this->scimDirectoryId        = $scimDirectoryId;
        $this->verifiedDomains        = $verifiedDomains;
        $this->groupRoleMap           = $groupRoleMap;
        $this->policyProfile          = $policyProfile;
        $this->rolloutVersion         = $rolloutVersion;
    }
}
