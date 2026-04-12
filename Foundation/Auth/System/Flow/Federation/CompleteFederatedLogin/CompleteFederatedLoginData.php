<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation\CompleteFederatedLogin;

final readonly class CompleteFederatedLoginData
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $connectionId,
        public array $payload,
        public string|null $ipAddress = null,
        public string|null $userAgent = null
    ) {}
}
