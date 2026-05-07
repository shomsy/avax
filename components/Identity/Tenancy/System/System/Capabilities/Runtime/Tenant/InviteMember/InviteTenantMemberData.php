<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\System\Capabilities\Runtime\Tenant\InviteMember;

use Avax\Components\Identity\Tenancy\System\System\Capabilities\Model\TenantMemberRole;
use SensitiveParameter;

final readonly class InviteTenantMemberData
{
    public function __construct(
        public string           $tenantSlug,
        #[SensitiveParameter]
        public string           $email,
        public TenantMemberRole $role,
        public string           $invitedBy,
    ) {}
}
