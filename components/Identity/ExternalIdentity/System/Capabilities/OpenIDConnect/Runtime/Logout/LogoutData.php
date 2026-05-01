<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutData
{
    public function __construct(
        #[SensitiveParameter]
        public ?string $sessionId = null,
        #[SensitiveParameter]
        public ?string $idTokenHint = null,
        #[SensitiveParameter]
        public ?string $logoutToken = null,
        public ?string $postLogoutRedirectUri = null,
        public ?string $state = null,
    ) {}
}
