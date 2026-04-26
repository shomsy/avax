<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadGroups;

use Avax\Auth\System\Capabilities\IdentitySync\SCIM\Runtime\ReadUsers\ReadScimUsers;

final readonly class ReadScimGroups
{
    public function __construct(private ReadScimUsers $readScimUsers) {}

    /**
     * @return list<ScimGroupProjection>
     */
    public function execute(string $directoryId) : array
    {
        $groups = [];

        foreach ($this->readScimUsers->execute(directoryId: $directoryId) as $user) {
            foreach ($user->groups as $group) {
                $groups[$group]['members'][] = new ScimGroupMember(
                    externalId: $user->externalId,
                    userId    : $user->userId,
                    username  : $user->username,
                    email     : $user->email
                );
            }
        }

        ksort(array: $groups);
        $projections = [];

        foreach ($groups as $groupId => $group) {
            $members = $group['members'];
            usort(
                array   : $members,
                callback: static fn (ScimGroupMember $left, ScimGroupMember $right) : int => strcmp(string1: $left->externalId, string2: $right->externalId)
            );
            $projections[] = new ScimGroupProjection(
                directoryId: $directoryId,
                groupId    : $groupId,
                displayName: $groupId,
                members    : $members
            );
        }

        return $projections;
    }
}
