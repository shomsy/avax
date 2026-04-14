<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\InviteMember;

use Avax\Auth\System\Capability\Tenant\TenantInvite;
use SensitiveParameter;

final readonly class IssuedTenantInvite
{
    public function __construct(
        public TenantInvite $invite,
        #[SensitiveParameter] public string $plainTextToken
    ) {}
}
