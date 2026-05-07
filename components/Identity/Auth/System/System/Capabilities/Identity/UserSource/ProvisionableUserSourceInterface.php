<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Identity\UserSource;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserRole;

/**
 * Extended user-source contract for lifecycle and provisioning mutations.
 */
interface ProvisionableUserSourceInterface extends UserSourceInterface
{
    public function updateEmail(UserId $userId, string $email) : void;

    /**
     * @param list<UserRole> $roles
     */
    public function replaceRoles(UserId $userId, array $roles) : void;

    /**
     * @param list<UserPermission> $permissions
     */
    public function replacePermissions(UserId $userId, array $permissions) : void;

    public function deactivate(UserId $userId) : void;

    public function activate(UserId $userId) : void;
}
