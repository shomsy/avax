<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Scim\ReadGroups;

/**
 * Projection of a SCIM group.
 */
final readonly class ScimGroupProjection
{
    /**
     * @param list<ScimGroupMember> $members
     */
    public function __construct(
        public string $id,
        public string $displayName,
        public array $members
    ) {}

    public function hasMembers() : bool
    {
        return $this->members !== [];
    }

    public function memberCount() : int
    {
        return count($this->members);
    }
}