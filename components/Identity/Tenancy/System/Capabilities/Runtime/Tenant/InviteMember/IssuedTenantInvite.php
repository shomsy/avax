<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\InviteMember;

use Avax\Components\Identity\Tenancy\System\Capabilities\Model\TenantInvite;
use SensitiveParameter;

final readonly class IssuedTenantInvite
{
    public function __construct(
        public TenantInvite $invite,
        #[SensitiveParameter]
        public string       $plainTextToken,
    ) {}
}
