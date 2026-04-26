<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Passkey\Runtime;

final readonly class PasskeyRegistration
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(public string $challengeId, public array $options) {}
}
