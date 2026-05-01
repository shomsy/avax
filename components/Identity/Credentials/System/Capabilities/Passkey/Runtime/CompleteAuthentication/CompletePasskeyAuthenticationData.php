<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteAuthentication;

use SensitiveParameter;

final readonly class CompletePasskeyAuthenticationData
{
    /**
     * @param  array<string, mixed>  $response
     */
    public function __construct(
        public string $challengeId,
        public array $response,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
