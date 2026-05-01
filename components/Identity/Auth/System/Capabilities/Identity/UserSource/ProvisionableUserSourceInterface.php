<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;

/**
 * Extended user-source contract for lifecycle and provisioning mutations.
 */
interface ProvisionableUserSourceInterface extends UserSourceInterface
{
    public function updateEmail(UserId $id, string $email): void;

    /**
     * @param list<UserRole> $roles
     */
    public function replaceRoles(UserId $id, array $roles): void;

    /**
     * @param list<UserPermission> $permissions
     */
    public function replacePermissions(UserId $id, array $permissions): void;

    public function deactivate(UserId $id): void;

    public function activate(UserId $id): void;
}
