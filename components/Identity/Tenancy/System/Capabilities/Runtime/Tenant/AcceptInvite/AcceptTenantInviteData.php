<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Capabilities\Runtime\Tenant\AcceptInvite;

use SensitiveParameter;

final readonly class AcceptTenantInviteData
{
    public function __construct(
        #[SensitiveParameter]
        public string $inviteToken,
        public int    $userId,
    ) {}
}
