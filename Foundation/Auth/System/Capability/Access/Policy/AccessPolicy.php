<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\Policy;

use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Declarative authorization policy evaluated inside the access capability.
 */
final readonly class AccessPolicy
{
    public IdentityPolicy|null $identityPolicy;
    public int|null            $freshMfaMaxAgeSeconds;
    public bool                $phishingResistantRequired;
    public bool                $adminElevation;
    public bool                $freshMfa;
    public int|null            $resourceOwnerUserId;
    public UserPermission|null $requiredPermission;
    public UserRole|null       $requiredRole;

    public function __construct(
        UserRole|null       $requiredRole = null,
        UserPermission|null $requiredPermission = null,
        int|null            $resourceOwnerUserId = null,
        bool|null           $freshMfa = null,
        bool|null           $adminElevation = null,
        bool|null           $phishingResistantRequired = null,
        int|null            $freshMfaMaxAgeSeconds = null,
        IdentityPolicy|null $identityPolicy = null
    )
    {
        $freshMfa                        ??= false;
        $adminElevation                  ??= false;
        $phishingResistantRequired       ??= false;
        $this->requiredRole              = $requiredRole;
        $this->requiredPermission        = $requiredPermission;
        $this->resourceOwnerUserId       = $resourceOwnerUserId;
        $this->freshMfa                  = $freshMfa;
        $this->adminElevation            = $adminElevation;
        $this->phishingResistantRequired = $phishingResistantRequired;
        $this->freshMfaMaxAgeSeconds     = $freshMfaMaxAgeSeconds;
        $this->identityPolicy            = $identityPolicy;
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
