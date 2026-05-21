<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\PublicSurface;

use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\System\Capabilities\IdentityRuntime\IdentityRuntime;
use Avax\Components\Identity\System\Configuration\Builders\IdentityRuntime as BuildIdentityRuntime;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;

/**
 * Identity — unified fluent entrypoint for all Identity sub-surfaces.
 *
 * Usage:
 *   Identity::tenancy()->getTenantId();
 *   Identity::credentials()->store($userId, $data);
 *   Identity::externalIdentity()->link($userId, 'google', $data);
 *   Identity::auth()->check();
 *   Identity::access()->allows('read.post');
 *   Identity::tokens()->issue('sub-123');
 *   Identity::risk()->assessCurrent();
 */
final class Identity
{
    /**
     * Tenancy surface — multi-tenant context management.
     */
    public static function tenancy() : Tenancy
    {
        return self::runtime()->tenancy();
    }

    /**
     * Credentials surface — user credential storage.
     */
    public static function credentials() : Credentials
    {
        return self::runtime()->credentials();
    }

    /**
     * External identity surface — OAuth/SSO identity linking.
     */
    public static function externalIdentity() : ExternalIdentity
    {
        return self::runtime()->externalIdentity();
    }

    /**
     * Auth surface — authentication operations.
     */
    public static function auth() : Auth
    {
        return self::runtime()->auth();
    }

    /**
     * Access surface — authorization and admin elevation.
     */
    public static function access() : Access
    {
        return self::runtime()->access();
    }

    /**
     * Tokens surface — token issuance and management.
     */
    public static function tokens() : Tokens
    {
        return self::runtime()->tokens();
    }

    /**
     * Risk surface — risk assessment.
     */
    public static function risk() : Risk
    {
        return self::runtime()->risk();
    }

    private static function runtime() : IdentityRuntime
    {
        return BuildIdentityRuntime::defaults()->runtime();
    }
}
