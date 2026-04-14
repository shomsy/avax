<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Tenant\AcceptInvite;

use SensitiveParameter;

final readonly class AcceptTenantInviteData
{
    public function __construct(
        #[SensitiveParameter] public string $inviteToken,
        public int $userId
    ) {}
}
