<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\FrontChannelLogout;

use SensitiveParameter;

final readonly class FrontChannelLogoutData
{
    public function __construct(
        #[SensitiveParameter]
        public ?string $sessionId = null,
        #[SensitiveParameter]
        public ?string $idTokenHint = null,
        public ?string $postLogoutRedirectUri = null,
        public ?string $state = null,
    ) {
    }
}
