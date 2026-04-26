<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\Policy;

use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;

/**
 * Declarative authorization policy evaluated inside the access capability.
 */
final readonly class AccessPolicy
{
    public bool $phishingResistantRequired;
    public bool $adminElevation;
    public bool $freshMfa;

    public function __construct(
        public UserRole|null       $requiredRole = null,
        public UserPermission|null $requiredPermission = null,
        public int|null            $resourceOwnerUserId = null,
        bool|null                  $freshMfa = null,
        bool|null                  $adminElevation = null,
        bool|null                  $phishingResistantRequired = null,
        public int|null            $freshMfaMaxAgeSeconds = null,
        public IdentityPolicy|null $identityPolicy = null
    )
    {
        $freshMfa                        ??= false;
        $adminElevation                  ??= false;
        $phishingResistantRequired       ??= false;
        $this->freshMfa                  = $freshMfa;
        $this->adminElevation            = $adminElevation;
        $this->phishingResistantRequired = $phishingResistantRequired;
    }

    public static function admin(
        UserPermission|null $requiredPermission = null,
        int|null            $resourceOwnerUserId = null
    ) : self
    {
        return self::forIdentityPolicy(
            identityPolicy     : IdentityPolicyCatalog::admin(),
            requiredRole       : UserRole::ADMIN,
            requiredPermission : $requiredPermission,
            resourceOwnerUserId: $resourceOwnerUserId
        );
    }

    public static function forIdentityPolicy(
        IdentityPolicy      $identityPolicy,
        UserRole|null       $requiredRole = null,
        UserPermission|null $requiredPermission = null,
        int|null            $resourceOwnerUserId = null
    ) : self
    {
        return new self(
            requiredRole             : $requiredRole,
            requiredPermission       : $requiredPermission,
            resourceOwnerUserId      : $resourceOwnerUserId,
            freshMfa                 : $identityPolicy->freshMfaMaxAgeSeconds !== null,
            adminElevation           : $identityPolicy->adminElevationRequired,
            phishingResistantRequired: $identityPolicy->phishingResistantRequired,
            freshMfaMaxAgeSeconds    : $identityPolicy->freshMfaMaxAgeSeconds,
            identityPolicy           : $identityPolicy
        );
    }

    public static function tenantAdmin(
        UserPermission|null $requiredPermission = null,
        int|null            $resourceOwnerUserId = null
    ) : self
    {
        return self::forIdentityPolicy(
            identityPolicy     : IdentityPolicyCatalog::tenantAdmin(),
            requiredRole       : UserRole::ADMIN,
            requiredPermission : $requiredPermission,
            resourceOwnerUserId: $resourceOwnerUserId
        );
    }
}
