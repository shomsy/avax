<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\BackChannelLogout;

use SensitiveParameter;

final readonly class BackChannelLogoutData
{
    public function __construct(
        #[SensitiveParameter]
        public string $logoutToken,
    ) {}
}
