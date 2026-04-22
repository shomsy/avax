<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\BackChannelLogout;

use SensitiveParameter;

final readonly class BackChannelLogoutData
{
    public function __construct(
        #[SensitiveParameter] public string $logoutToken
    )
    {
    }
}
