<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Tenant\InviteMember;

use Avax\Auth\System\Capabilities\Tenant\TenantInvite;
use SensitiveParameter;

final readonly class IssuedTenantInvite
{
    public string       $plainTextToken;
    public TenantInvite $invite;

    public function __construct(
        TenantInvite                 $invite,
        #[SensitiveParameter] string $plainTextToken
    )
    {
        $this->invite         = $invite;
        $this->plainTextToken = $plainTextToken;
    }
}
