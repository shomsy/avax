<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\ResetIdentityRuntime;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;

final readonly class ResetIdentityRuntime
{
    /** @param list<ResettableIdentityState> $state */
    public function __construct(private array $state) {}

    public function reset(): void
    {
        foreach ($this->state as $state) {
            $state->reset();
        }
    }
}
