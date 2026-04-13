<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadGroups;

/**
 * Member of a SCIM group.
 */
final readonly class ScimGroupMember
{
    public function __construct(
        public string $value,
        public string $display,
        public string $type = 'user'
    ) {}
}