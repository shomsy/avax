<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutData
{
    public function __construct(
        #[SensitiveParameter] public string|null $sessionId = null,
        #[SensitiveParameter] public string|null $idTokenHint = null,
        #[SensitiveParameter] public string|null $logoutToken = null,
        public string|null                       $postLogoutRedirectUri = null,
        public string|null                       $state = null
    )
    {
    }
}
