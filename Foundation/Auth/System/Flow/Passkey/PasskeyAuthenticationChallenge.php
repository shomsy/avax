<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey;

final readonly class PasskeyAuthenticationChallenge
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $challengeId,
        public array $options
    ) {}
}
