<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capabilities\Access\AccessInterface;
use Avax\Auth\System\Capabilities\Diagnostics\Diagnostics;
use Avax\Auth\System\Capabilities\ExternalIdentity\ExternalIdentity;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\IdentitySync\IdentitySync;
use Avax\Auth\System\Capabilities\Tenancy\Tenancy;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Auth\System\Flows\Login\Credentials;

/**
 * Contract for the core authentication system.
 *
 * Provides high-frequency convenience methods directly and exposes
 * capability owners via accessor methods for domain-specific operations.
 */
interface AuthInterface
{
    // ── Fast-path convenience methods ──

    public function login(Credentials $credentials) : AuthenticationResult;

    public function logout() : void;

    public function authenticateRequest(AuthenticationRequest $request) : AuthenticationContext;

    public function current() : AuthenticationContext;

    public function check() : bool;

    public function user() : AuthenticatedUser|null;

    // ── Capability accessors ──

    public function access() : AccessInterface;

    public function identity() : Identity;

    public function externalIdentity() : ExternalIdentity;

    public function identitySync() : IdentitySync;

    public function tenancy() : Tenancy;

    public function diagnostics() : Diagnostics;
}
