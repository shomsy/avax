<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final readonly class StartedFederatedLogin
{
    public string|null $state;
    public string      $redirectUrl;

    public function __construct(
        string      $redirectUrl,
        string|null $state = null
    )
    {
        $this->redirectUrl = $redirectUrl;
        $this->state       = $state;
    }
}
