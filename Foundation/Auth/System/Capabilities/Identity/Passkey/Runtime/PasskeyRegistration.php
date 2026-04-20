<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Passkey;

final readonly class PasskeyRegistration
{
    /** @var array<string, mixed> */
    public array  $options;
    public string $challengeId;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $challengeId,
        array  $options
    )
    {
        $this->challengeId = $challengeId;
        $this->options     = $options;
    }
}
