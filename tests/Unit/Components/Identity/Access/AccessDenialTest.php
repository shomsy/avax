<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

/**
 * Negative tests for Access component denial paths.
 *
 * Each test proves that the correct exception is thrown when the
 * access requirement is NOT met. These are security boundary tests.
 */
final class AccessDenialTest extends TestCase
{
    private CurrentAuthentication $currentAuth;

    protected function setUp() : void
    {
        $this->currentAuth = new CurrentAuthentication();
    }

    protected function tearDown() : void
    {
        $this->currentAuth->clear();
    }

    // === RequireAuthentication Denial ===

    public function test_require_authentication_denies_when_not_authenticated() : void
    {
        $requireAuth = new RequireAuthentication($this->currentAuth);

        $this->expectException(Unauthenticated::class);
        $requireAuth->execute();
    }

    public function test_require_authentication_allows_when_authenticated() : void
    {
        $requireAuth = new RequireAuthentication($this->currentAuth);

        // Authenticate a user
        $user = $this->createAuthenticatedUser();
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        // Should not throw
        $requireAuth->execute();
        $this->assertTrue($this->currentAuth->read()->isAuthenticated());
    }

    // === RequireRole Denial ===

    public function test_require_role_denies_when_not_authenticated() : void
    {
        $requireRole = new RequireRole($this->currentAuth);

        $this->expectException(Unauthenticated::class);
        $requireRole->execute(UserRole::ADMIN);
    }

    public function test_require_role_denies_when_user_lacks_role() : void
    {
        $requireRole = new RequireRole($this->currentAuth);

        // Authenticate a non-admin user
        $user = $this->createAuthenticatedUser(roles: [UserRole::USER]);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        $this->expectException(RoleDenied::class);
        $requireRole->execute(UserRole::ADMIN);
    }

    public function test_require_role_allows_when_user_has_role() : void
    {
        $requireRole = new RequireRole($this->currentAuth);

        $user = $this->createAuthenticatedUser(roles: [UserRole::ADMIN]);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        // Should not throw
        $requireRole->execute(UserRole::ADMIN);
        $this->assertTrue($this->currentAuth->read()->isAuthenticated());
    }

    // === RequirePermission Denial ===

    public function test_require_permission_denies_when_not_authenticated() : void
    {
        $requirePermission = new RequirePermission($this->currentAuth);

        $this->expectException(Unauthenticated::class);
        $requirePermission->execute(new UserPermission('users.delete'));
    }

    public function test_require_permission_denies_when_user_lacks_permission() : void
    {
        $requirePermission = new RequirePermission($this->currentAuth);

        $user = $this->createAuthenticatedUser(permissions: [new UserPermission('users.read')]);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        $this->expectException(PermissionDenied::class);
        $requirePermission->execute(new UserPermission('users.delete'));
    }

    public function test_require_permission_allows_when_user_has_permission() : void
    {
        $requirePermission = new RequirePermission($this->currentAuth);

        $permission = new UserPermission('users.delete');
        $user = $this->createAuthenticatedUser(permissions: [$permission]);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        // Should not throw
        $requirePermission->execute($permission);
        $this->assertTrue($this->currentAuth->read()->isAuthenticated());
    }

    // === RequireResourceOwner Denial ===

    public function test_require_resource_owner_denies_when_not_authenticated() : void
    {
        $requireOwner = new RequireResourceOwner($this->currentAuth);

        $this->expectException(Unauthenticated::class);
        $requireOwner->execute(ownerUserId: 1);
    }

    public function test_require_resource_owner_denies_when_user_is_not_owner() : void
    {
        $requireOwner = new RequireResourceOwner($this->currentAuth);

        $user = $this->createAuthenticatedUser(userId: 5);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        $this->expectException(ResourceOwnerDenied::class);
        $requireOwner->execute(ownerUserId: 10);
    }

    public function test_require_resource_owner_allows_when_user_is_owner() : void
    {
        $requireOwner = new RequireResourceOwner($this->currentAuth);

        $userId = 42;
        $user = $this->createAuthenticatedUser(userId: $userId);
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        // Should not throw
        $requireOwner->execute(ownerUserId: $userId);
        $this->assertTrue($this->currentAuth->read()->isAuthenticated());
    }

    // === RequirePhishingResistantAuthentication Denial ===

    public function test_require_phishing_resistant_denies_when_not_authenticated() : void
    {
        $requirePhishingResistant = new RequirePhishingResistantAuthentication($this->currentAuth);

        $this->expectException(Unauthenticated::class);
        $requirePhishingResistant->execute();
    }

    public function test_require_phishing_resistant_denies_when_auth_is_not_phishing_resistant() : void
    {
        $requirePhishingResistant = new RequirePhishingResistantAuthentication($this->currentAuth);

        // Authenticate via session (not phishing-resistant)
        $user = $this->createAuthenticatedUser();
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::SESSION,
            sessionId: 'test-session',
        ));

        $this->expectException(PhishingResistantAuthenticationRequired::class);
        $requirePhishingResistant->execute();
    }

    public function test_require_phishing_resistant_allows_when_auth_is_phishing_resistant() : void
    {
        $requirePhishingResistant = new RequirePhishingResistantAuthentication($this->currentAuth);

        // Authenticate with phishing-resistant flag set
        $user = $this->createAuthenticatedUser();
        $this->currentAuth->store(AuthenticationContext::authenticated(
            authenticatedUser: $user,
            authenticationMode: AuthenticationMode::TOKEN,
            phishingResistant: true,
        ));

        // Should not throw
        $requirePhishingResistant->execute();
        $this->assertTrue($this->currentAuth->read()->isAuthenticated());
    }

    // === AuthorizationEngine Denial ===

    public function test_authorization_engine_denies_unknown_permission() : void
    {
        $engine = new AuthorizationEngine();

        $this->assertFalse($engine->check('users.delete'));
    }

    public function test_authorization_engine_denies_empty_permission() : void
    {
        $engine = new AuthorizationEngine();

        $this->assertFalse($engine->check(''));
    }

    public function test_authorization_engine_allows_granted_permission() : void
    {
        $engine = new AuthorizationEngine();
        $engine->grant('users.read');

        $this->assertTrue($engine->check('users.read'));
    }

    public function test_authorization_engine_allows_wildcard_permission() : void
    {
        $engine = new AuthorizationEngine();
        $engine->grant('*');

        $this->assertTrue($engine->check('any.permission.here'));
    }

    public function test_authorization_engine_allows_resource_scoped_permission() : void
    {
        $engine = new AuthorizationEngine();
        $engine->grant('documents.view:123');

        $this->assertTrue($engine->check('documents.view', resource: 123));
    }

    public function test_authorization_engine_denies_resource_scoped_when_wrong_resource() : void
    {
        $engine = new AuthorizationEngine();
        $engine->grant('documents.view:123');

        $this->assertFalse($engine->check('documents.view', resource: 456));
    }

    public function test_authorization_engine_respects_default_allow() : void
    {
        $engine = new AuthorizationEngine(defaultAllow: true);

        $this->assertTrue($engine->check('unknown.permission'));
    }

    public function test_authorization_engine_respects_default_deny() : void
    {
        $engine = new AuthorizationEngine(defaultAllow: false);

        $this->assertFalse($engine->check('unknown.permission'));
    }

    public function test_authorization_engine_revokes_permission() : void
    {
        $engine = new AuthorizationEngine();
        $engine->grant('users.delete');
        $this->assertTrue($engine->check('users.delete'));

        $engine->revoke('users.delete');
        $this->assertFalse($engine->check('users.delete'));
    }

    public function test_authorization_engine_initializes_from_iterable() : void
    {
        $permissions = ['users.read', 'users.write', '*:admin'];
        $engine = new AuthorizationEngine($permissions);

        $this->assertTrue($engine->check('users.read'));
        $this->assertTrue($engine->check('users.write'));
        $this->assertFalse($engine->check('users.delete'));
    }

    // === Helper ===

    /**
     * @param list<UserRole> $roles
     * @param list<UserPermission> $permissions
     */
    private function createAuthenticatedUser(
        int $userId = 1,
        array $roles = [],
        array $permissions = [],
    ) : AuthenticatedUser {
        $roleValues = array_map(
            static fn (UserRole $role) => $role->value,
            $roles,
        );
        $permissionValues = array_map(
            static fn (UserPermission $permission) => $permission->value,
            $permissions,
        );

        return new AuthenticatedUser(
            id: $userId,
            email: 'user@example.com',
            username: 'testuser',
            roles: $roleValues,
            permissions: $permissionValues,
            emailVerified: true,
            mfaEnabled: false,
        );
    }
}
