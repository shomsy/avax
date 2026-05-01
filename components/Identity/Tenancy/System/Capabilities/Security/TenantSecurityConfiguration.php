<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Security;

final readonly class TenantSecurityConfiguration
{
    public string $policyProfile;

    /** @var array<string, list<string>> */
    public array $groupRoleMap;

    /** @var list<string> */
    public array $verifiedDomains;

    /**
     * @param list<string>                $verifiedDomains
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $tenantSlug,
        public ?string $federationConnectionId = null,
        public ?string $scimDirectoryId = null,
        ?array  $verifiedDomains = null,
        ?array  $groupRoleMap = null,
        ?string $policyProfile = null,
        public int $rolloutVersion = 1,
    ) {
        $verifiedDomains ??= [];
        $groupRoleMap ??= [];
        $policyProfile ??= 'user';
        $this->verifiedDomains = $verifiedDomains;
        $this->groupRoleMap = $groupRoleMap;
        $this->policyProfile = $policyProfile;
    }
}
