<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\TenantSecurity;

final readonly class TenantSecurityConfiguration
{
    /**
     * @param list<string> $verifiedDomains
     * @param array<string, list<string>> $groupRoleMap
     */
    public function __construct(
        public string $tenantSlug,
        public string|null $federationConnectionId = null,
        public string|null $scimDirectoryId = null,
        public array $verifiedDomains = [],
        public array $groupRoleMap = [],
        public string $policyProfile = 'user',
        public int $rolloutVersion = 1
    ) {}
}
