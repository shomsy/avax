<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\InviteMember;

use Avax\Auth\System\Capability\Tenant\TenantMemberRole;

final readonly class InviteTenantMemberData
{
    public function __construct(
        public string $tenantSlug,
        public string $email,
        public TenantMemberRole $role,
        public string $invitedBy
    ) {}
}
