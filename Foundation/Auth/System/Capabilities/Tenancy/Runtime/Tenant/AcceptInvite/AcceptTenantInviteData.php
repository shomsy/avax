<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Tenant\AcceptInvite;

use SensitiveParameter;

final readonly class AcceptTenantInviteData
{
    public int    $userId;
    public string $inviteToken;

    public function __construct(
        #[SensitiveParameter] string $inviteToken,
        int                          $userId
    )
    {
        $this->inviteToken = $inviteToken;
        $this->userId      = $userId;
    }
}
