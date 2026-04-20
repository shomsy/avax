<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Oidc\BackChannelLogout;

use SensitiveParameter;

final readonly class BackChannelLogoutData
{
    public string $logoutToken;

    public function __construct(
        #[SensitiveParameter] string $logoutToken
    )
    {
        $this->logoutToken = $logoutToken;
    }
}
