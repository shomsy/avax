<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadGroups;

final readonly class ScimGroupMember
{
    public function __construct(
        public string $externalId,
        public int $userId,
        public string $username,
        public string $email
    ) {}
}
