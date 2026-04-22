<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Security;

final readonly class TenantSecurityConfiguration
{
    public string      $policyProfile;
    /** @var array<string, list<string>> */
    public array       $groupRoleMap;
    /** @var list<string> */
    public array       $verifiedDomains;

    /**
     * @param list<string>                $verifiedDomains
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string      $tenantSlug,
        public string|null $federationConnectionId = null,
        public string|null $scimDirectoryId = null,
        array|null  $verifiedDomains = null,
        array|null  $groupRoleMap = null,
        string|null $policyProfile = null,
        public int         $rolloutVersion = 1
    )
    {
        $verifiedDomains              ??= [];
        $groupRoleMap                 ??= [];
        $policyProfile                ??= 'user';
        $this->verifiedDomains        = $verifiedDomains;
        $this->groupRoleMap           = $groupRoleMap;
        $this->policyProfile          = $policyProfile;
    }
}
