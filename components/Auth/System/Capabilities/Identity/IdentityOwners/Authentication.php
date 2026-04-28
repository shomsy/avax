<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\IdentityOwners;

use Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthentication;
use Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationFailed;
use Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationRequest;
use Avax\Components\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Components\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Auth\System\Flows\Login\Credentials;
use Avax\Components\Auth\System\Flows\Login\Login;
use Avax\Components\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Auth\System\Flows\Logout\Logout;
use DateMalformedStringException;
use SensitiveParameter;

final readonly class Authentication
{
    public function __construct(
        private Login                                       $login,
        private Logout                                      $logout,
        #[SensitiveParameter] private RefreshAuthentication $refreshAuthentication
    ) {}

    /**
     * @throws AuthenticationFailed
     * @throws RateLimitException
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : AuthenticationResult
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    /**
     * @throws RefreshAuthenticationFailed
     * @throws DateMalformedStringException
     */
    public function refresh(RefreshAuthenticationRequest $request) : AuthenticationResult
    {
        return $this->refreshAuthentication->execute(request: $request);
    }
}
