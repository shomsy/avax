<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\StartFederatedLogin;

final readonly class StartFederatedLoginData
{
    public string|null $state;
    public string      $redirectUri;
    public string      $connectionId;

    public function __construct(
        string      $connectionId,
        string      $redirectUri,
        string|null $state = null
    )
    {
        $this->connectionId = $connectionId;
        $this->redirectUri  = $redirectUri;
        $this->state        = $state;
    }
}
