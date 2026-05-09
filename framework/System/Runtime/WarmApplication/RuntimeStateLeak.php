<?php

declare(strict_types=1);

namespace Avax\Framework\System\Runtime\WarmApplication;

/**
 * RuntimeStateLeak — Describes a specific state leak detected between requests.
 */
final readonly class RuntimeStateLeak
{
    public function __construct(
        public MustResetState $leakedState,
        public string $detail = '',
    ) {
    }

    public function description(): string
    {
        $detail = $this->detail !== '' ? ": {$this->detail}" : '';

        return "Leaked {$this->leakedState->value}{$detail}";
    }
}
