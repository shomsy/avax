<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups;

use SensitiveParameter;

final readonly class ScimGroupMember
{
    public function __construct(
        public string                       $externalId,
        public int                          $userId,
        public string                       $username,
        #[SensitiveParameter] public string $email
    ) {}
}
