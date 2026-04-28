<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember;

use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use SensitiveParameter;

final readonly class InviteTenantMemberData
{
    public function __construct(
        public string                       $tenantSlug,
        #[SensitiveParameter] public string $email,
        public TenantMemberRole             $role,
        public string                       $invitedBy
    ) {}
}
