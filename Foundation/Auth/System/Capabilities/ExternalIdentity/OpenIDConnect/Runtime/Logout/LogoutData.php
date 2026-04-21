<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutData
{
    public string|null $state;
    public string|null $postLogoutRedirectUri;
    public string|null $logoutToken;
    public string|null $idTokenHint;
    public string|null $sessionId;

    public function __construct(
        #[SensitiveParameter] string|null $sessionId = null,
        #[SensitiveParameter] string|null $idTokenHint = null,
        #[SensitiveParameter] string|null $logoutToken = null,
        string|null                       $postLogoutRedirectUri = null,
        string|null                       $state = null
    )
    {
        $this->sessionId             = $sessionId;
        $this->idTokenHint           = $idTokenHint;
        $this->logoutToken           = $logoutToken;
        $this->postLogoutRedirectUri = $postLogoutRedirectUri;
        $this->state                 = $state;
    }
}
