<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutResult
{
    public function __construct(
        public bool        $revoked,
        #[SensitiveParameter]
        public string|null $sessionId = null,
        public string|null $postLogoutRedirectUri = null,
        public string|null $state = null,
    ) {}
}
