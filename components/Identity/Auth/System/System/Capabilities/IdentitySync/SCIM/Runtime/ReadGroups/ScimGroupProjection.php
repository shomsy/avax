<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups;

final readonly class ScimGroupProjection
{
    /**
     * @param list<ScimGroupMember> $members
     */
    public function __construct(public string $directoryId, public string $groupId, public string $displayName, public array $members) {}
}
