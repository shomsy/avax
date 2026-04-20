<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\SingleSignOn\FederationSupport;

use Avax\Auth\System\Capabilities\Identity\User\UserRole;

/**
 * Validates tenant-owned federation group-to-role mappings.
 */
final readonly class GroupRoleMappingValidator
{
    /**
     * @param array<string, list<string>> $groupRoleMap
     */
    public function isValid(array $groupRoleMap) : bool
    {
        foreach ($groupRoleMap as $group => $roles) {
            if (trim($group) === '') {
                return false;
            }

            foreach ($roles as $roleValue) {
                if (UserRole::tryFrom(value: $roleValue) === null) {
                    return false;
                }
            }
        }

        return true;
    }
}
