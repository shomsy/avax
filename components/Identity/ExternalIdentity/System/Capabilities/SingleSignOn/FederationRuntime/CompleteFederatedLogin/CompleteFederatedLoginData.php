<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\SingleSignOn\FederationRuntime\CompleteFederatedLogin;

use SensitiveParameter;

final readonly class CompleteFederatedLoginData
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string  $connectionId,
        public array   $payload,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
