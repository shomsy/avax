<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\StateReset;

interface ResettableState
{
    public function resetState(): void;
}
