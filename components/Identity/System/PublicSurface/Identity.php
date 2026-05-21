<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\PublicSurface;

/**
 * Identity — unified fluent entrypoint for all Identity sub-surfaces.
 *
 * Usage:
 *   Identity::tenancy()->getTenantId();
 *   Identity::credentials()->store($userId, $data);
 *   Identity::externalIdentity()->link($userId, 'google', $data);
 *
 * Auth, Access, and Tokens sub-surfaces require Builder/Graph assembly
 * before they can be accessed through this DSL.
 */
final class Identity
{
    /**
     * Tenancy surface — multi-tenant context management.
     */
    public static function tenancy() : \Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy
    {
        return new \Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy();
    }

    /**
     * Credentials surface — user credential storage.
     */
    public static function credentials() : \Avax\Components\Identity\Credentials\System\PublicSurface\Credentials
    {
        return new \Avax\Components\Identity\Credentials\System\PublicSurface\Credentials();
    }

    /**
     * External identity surface — OAuth/SSO identity linking.
     */
    public static function externalIdentity() : \Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity
    {
        return new \Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity();
    }
}
