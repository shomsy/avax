<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\PublicSurface;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity as AuthIdentity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;
use DateTimeImmutable;

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

    /**
     * Auth surface — authentication operations.
     */
    public static function auth() : Auth
    {
        $nullSession = new class implements SessionIdentityInterface
        {
            public function issue(int $userId, ?DateTimeImmutable $mfaVerifiedAt = null, bool $phishingResistant = false) : string|null
            {
                return null;
            }

            public function captureCurrentSession(?string $ipAddress = null, ?string $userAgent = null) : void {}

            public function resolveUserId() : int|null
            {
                return null;
            }

            public function resolveMfaVerifiedAt() : ?DateTimeImmutable
            {
                return null;
            }

            public function resolvePhishingResistant() : bool
            {
                return false;
            }

            public function currentSessionId() : string|null
            {
                return null;
            }

            public function clear() : void {}
        };

        return new Auth(
            identity: AuthIdentity::fromBackends(
                sessionIdentity: $nullSession,
            ),
        );
    }

    /**
     * Access surface — authorization and admin elevation.
     */
    public static function access() : Access
    {
        $begin = new BeginAdminElevation();

        return new Access(
            authorizationEngine: new AuthorizationEngine(),
            beginAdminElevation: $begin,
            endAdminElevation  : new EndAdminElevation(
                beginAdminElevation: $begin,
            ),
        );
    }

    /**
     * Tokens surface — token issuance and management.
     */
    public static function tokens() : Tokens
    {
        return Tokens::hmac(secret: 'test');
    }

    /**
     * Risk surface — risk assessment.
     */
    public static function risk() : Risk
    {
        return new Risk();
    }
}
