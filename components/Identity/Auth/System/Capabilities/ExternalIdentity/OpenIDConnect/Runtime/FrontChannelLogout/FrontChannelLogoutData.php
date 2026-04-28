<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OpenIDConnect\Runtime\FrontChannelLogout;

use SensitiveParameter;

final readonly class FrontChannelLogoutData
{
    public function __construct(
        #[SensitiveParameter] public string|null $sessionId = null,
        #[SensitiveParameter] public string|null $idTokenHint = null,
        public string|null                       $postLogoutRedirectUri = null,
        public string|null                       $state = null
    ) {}
}
