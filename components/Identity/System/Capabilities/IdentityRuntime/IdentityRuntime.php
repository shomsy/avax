<?php

declare(strict_types=1);

namespace Avax\Components\Identity\System\Capabilities\IdentityRuntime;

use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Auth\System\PublicSurface\Auth;
use Avax\Components\Identity\Credentials\System\PublicSurface\Credentials;
use Avax\Components\Identity\ExternalIdentity\System\PublicSurface\ExternalIdentity;
use Avax\Components\Identity\Risk\System\PublicSurface\Risk;
use Avax\Components\Identity\Tenancy\System\PublicSurface\Tenancy;
use Avax\Components\Identity\Tokens\System\PublicSurface\Tokens;

/**
 * IdentityRuntime owns the root Identity DSL delegation targets.
 */
final readonly class IdentityRuntime
{
    public function __construct(
        private Auth             $auth,
        private Access           $access,
        private Credentials      $credentials,
        private Tokens           $tokens,
        private Tenancy          $tenancy,
        private Risk             $risk,
        private ExternalIdentity $externalIdentity,
    ) {}

    public function auth() : Auth
    {
        return $this->auth;
    }

    public function access() : Access
    {
        return $this->access;
    }

    public function credentials() : Credentials
    {
        return $this->credentials;
    }

    public function tokens() : Tokens
    {
        return $this->tokens;
    }

    public function tenancy() : Tenancy
    {
        return $this->tenancy;
    }

    public function risk() : Risk
    {
        return $this->risk;
    }

    public function externalIdentity() : ExternalIdentity
    {
        return $this->externalIdentity;
    }
}
