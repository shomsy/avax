<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember;

use Avax\Auth\System\Capabilities\Tenancy\Model\TenantMemberRole;
use SensitiveParameter;

final readonly class InviteTenantMemberData
{
    public string           $invitedBy;
    public TenantMemberRole $role;
    public string           $email;
    public string           $tenantSlug;

    public function __construct(
        string                       $tenantSlug,
        #[SensitiveParameter] string $email,
        TenantMemberRole             $role,
        string                       $invitedBy
    )
    {
        $this->tenantSlug = $tenantSlug;
        $this->email      = $email;
        $this->role       = $role;
        $this->invitedBy  = $invitedBy;
    }
}
