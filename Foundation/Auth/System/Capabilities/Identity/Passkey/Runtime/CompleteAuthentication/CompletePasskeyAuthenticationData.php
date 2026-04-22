<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime\CompleteAuthentication;

use SensitiveParameter;

final readonly class CompletePasskeyAuthenticationData
{
    /**
     * @param array<string, mixed> $response
     */
    public function __construct(
        public string                            $challengeId,
        public array                             $response,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    )
    {
    }
}
