<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\System;

use Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation\AccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\System\PublicSurface\Identity;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Target DSL characterization tests for the Identity fluent public API.
 *
 * These tests define the future public surface contract.
 * Some sub-surfaces may not exist yet — marked incomplete.
 */
final class IdentityTargetDslCharacterizationTest extends TestCase
{
    // ── Identity root DSL ──────────────────────────────────────────

    #[Test]
    public function tenancyReturnsTenancySurface(): void
    {
        $tenancy = Identity::tenancy();
        self::assertInstanceOf(Tenancy::class, $tenancy);
    }

    #[Test]
    public function credentialsReturnsCredentialsSurface(): void
    {
        $credentials = Identity::credentials();
        self::assertInstanceOf(Credentials::class, $credentials);
    }

    #[Test]
    public function externalIdentityReturnsExternalIdentitySurface(): void
    {
        $externalIdentity = Identity::externalIdentity();
        self::assertInstanceOf(ExternalIdentity::class, $externalIdentity);
    }

    #[Test]
    public function authReturnsAuthSurface(): void
    {
        Identity::auth(); // must not throw
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function accessReturnsAccessSurface(): void
    {
        Identity::access(); // must not throw
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function tokensReturnsTokensSurface(): void
    {
        Identity::tokens(); // must not throw
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function riskReturnsRiskSurface(): void
    {
        Identity::risk(); // must not throw
        self::expectNotToPerformAssertions();
    }

    // ── Auth surface DSL ───────────────────────────────────────────

    #[Test]
    public function authCheckReturnsBool(): void
    {
        $result = Identity::auth()->check();
        self::assertIsBool($result);
    }

    #[Test]
    public function authUserReturnsNullable(): void
    {
        $user = Identity::auth()->user();
        self::assertNull($user);
    }

    #[Test]
    public function authGuestReturnsBool(): void
    {
        self::assertIsBool(Identity::auth()->guest());
    }

    #[Test]
    public function authLogoutReturnsVoid(): void
    {
        Identity::auth()->logout();
        self::expectNotToPerformAssertions();
    }

    // ── Access surface DSL ─────────────────────────────────────────

    #[Test]
    public function accessRequireAuthenticationReturnsVoid(): void
    {
        Identity::access()->requireAuthentication();
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function accessRequireRoleReturnsVoid(): void
    {
        Identity::access()->requireRole(UserRole::ADMIN);
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function accessRequirePermissionReturnsVoid(): void
    {
        Identity::access()->requirePermission(UserPermission::MANAGE_USERS);
        self::expectNotToPerformAssertions();
    }

    // ── Credentials sub-surfaces ───────────────────────────────────

    #[Test]
    public function credentialsMfaReturnsMfaSurface(): void
    {
        $mfa = Identity::credentials()->mfa();
        self::assertNotNull($mfa);
    }

    #[Test]
    public function credentialsPasskeysReturnsPasskeysSurface(): void
    {
        $passkeys = Identity::credentials()->passkeys();
        self::assertNotNull($passkeys);
    }

    // ── Tokens surface DSL ─────────────────────────────────────────

    #[Test]
    public function tokensIssueReturnsIssuedToken(): void
    {
        Identity::tokens()->issue('sub-123');
        self::expectNotToPerformAssertions();
    }

    // ── Tenancy sub-surfaces ───────────────────────────────────────

    #[Test]
    public function tenancyAdminReturnsAdminSurface(): void
    {
        $admin = Identity::tenancy()->admin();
        self::assertNotNull($admin);
    }

    #[Test]
    public function tenancyAdminBeginElevationReturnsVoid(): void
    {
        Identity::tenancy()->admin()->beginElevation();
        self::expectNotToPerformAssertions();
    }

    // ── Risk surface DSL ───────────────────────────────────────────

    #[Test]
    public function riskAssessCurrentReturnsNullable(): void
    {
        $decision = Identity::risk()->assessCurrent();
        self::assertNull($decision);
    }

    // ── ExternalIdentity surface DSL ───────────────────────────────

    #[Test]
    public function externalIdentityLinkReturnsVoid(): void
    {
        Identity::externalIdentity()->link('user-1', 'google', ['id' => 'g123']);
        self::expectNotToPerformAssertions();
    }

    #[Test]
    public function externalIdentityResolveReturnsNullable(): void
    {
        $result = Identity::externalIdentity()->resolve('user-1', 'google');
        self::assertNull($result);
    }
}
