<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Passkey\Runtime\CompleteRegistration;

use SensitiveParameter;

final readonly class CompletePasskeyRegistrationData
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
