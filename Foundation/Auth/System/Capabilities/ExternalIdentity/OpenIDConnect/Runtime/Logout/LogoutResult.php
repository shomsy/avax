<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutResult
{
    public string|null $state;
    public string|null $postLogoutRedirectUri;
    public string|null $sessionId;
    public bool        $revoked;

    public function __construct(
        bool                              $revoked,
        #[SensitiveParameter] string|null $sessionId = null,
        string|null                       $postLogoutRedirectUri = null,
        string|null                       $state = null
    )
    {
        $this->revoked               = $revoked;
        $this->sessionId             = $sessionId;
        $this->postLogoutRedirectUri = $postLogoutRedirectUri;
        $this->state                 = $state;
    }
}
