<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadGroups;

final readonly class ScimGroupProjection
{
    public array  $members;
    public string $displayName;
    public string $groupId;
    public string $directoryId;

    /**
     * @param list<ScimGroupMember> $members
     */
    public function __construct(
        string $directoryId,
        string $groupId,
        string $displayName,
        array  $members
    )
    {
        $this->directoryId = $directoryId;
        $this->groupId     = $groupId;
        $this->displayName = $displayName;
        $this->members     = $members;
    }
}
