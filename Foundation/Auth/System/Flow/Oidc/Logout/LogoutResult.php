<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\Logout;

final readonly class LogoutResult
{
    public function __construct(
        public bool $revoked,
        public string|null $sessionId = null,
        public string|null $postLogoutRedirectUri = null,
        public string|null $state = null
    ) {}
}
