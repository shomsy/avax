<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Tenancy\Runtime\Tenant\InviteMember;

use Avax\Auth\System\Capabilities\Tenancy\Model\TenantInvite;
use SensitiveParameter;

final readonly class IssuedTenantInvite
{
    public function __construct(
        public TenantInvite                 $invite,
        #[SensitiveParameter] public string $plainTextToken
    )
    {
    }
}
