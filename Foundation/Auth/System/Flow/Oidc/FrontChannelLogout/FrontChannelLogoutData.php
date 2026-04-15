<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\FrontChannelLogout;

use SensitiveParameter;

final readonly class FrontChannelLogoutData
{
    public string|null $state;
    public string|null $postLogoutRedirectUri;
    public string|null $idTokenHint;
    public string|null $sessionId;

    public function __construct(
        #[SensitiveParameter] string|null $sessionId = null,
        #[SensitiveParameter] string|null $idTokenHint = null,
        string|null                       $postLogoutRedirectUri = null,
        string|null                       $state = null
    )
    {
        $this->sessionId             = $sessionId;
        $this->idTokenHint           = $idTokenHint;
        $this->postLogoutRedirectUri = $postLogoutRedirectUri;
        $this->state                 = $state;
    }
}
