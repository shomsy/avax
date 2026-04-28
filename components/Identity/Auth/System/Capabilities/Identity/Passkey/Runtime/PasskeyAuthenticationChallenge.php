<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Passkey\Runtime;

final readonly class PasskeyAuthenticationChallenge
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(public string $challengeId, public array $options) {}
}
