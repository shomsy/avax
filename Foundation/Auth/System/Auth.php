<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\Access;
use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth.
 *
 * Exposes the most common authentication operations directly.
 * For capability-specific operations, navigate to the owning capability:
 *
 *   $auth->identity()->beginPasskeyRegistration();
 *   $auth->externalIdentity()->registerOAuthClient($data);
 *   $auth->tenancy()->createTenant($data);
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Access           $access,
        private Diagnostics      $diagnostics,
        private Identity         $identity,
        private ExternalIdentity $externalIdentity,
        private IdentitySync     $identitySync,
        private Tenancy          $tenancy
    ) {}

    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    // ── Fast-path convenience methods (high-frequency, cross-cutting) ──

    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->identity->login(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->identity->logout();
    }

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext
    {
        return $this->access->authenticateRequest(request: $request);
    }

    public function current() : AuthenticationContext
    {
        return $this->access->current();
    }

    public function check() : bool
    {
        return $this->access->check();
    }

    public function user() : AuthenticatedUser|null
    {
        return $this->access->user();
    }

    // ── Capability accessors ──

    public function access() : AccessInterface
    {
        return $this->access->access();
    }

    public function identity() : Identity
    {
        return $this->identity;
    }

    public function externalIdentity() : ExternalIdentity
    {
        return $this->externalIdentity;
    }

    public function identitySync() : IdentitySync
    {
        return $this->identitySync;
    }

    public function tenancy() : Tenancy
    {
        return $this->tenancy;
    }

    public function diagnostics() : Diagnostics
    {
        return $this->diagnostics;
    }
}
