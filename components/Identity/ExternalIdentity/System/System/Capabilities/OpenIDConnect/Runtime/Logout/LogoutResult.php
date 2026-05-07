<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\Logout;

use SensitiveParameter;

final readonly class LogoutResult
{
    public function __construct(
        public bool    $revoked,
        #[SensitiveParameter]
        public ?string $sessionId = null,
        public ?string $postLogoutRedirectUri = null,
        public ?string $state = null,
    ) {}
}
