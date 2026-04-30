<?php

declare(strict_types=1);

namespace Avax\Components\Concurrency\System\Capabilities\Cancellation;

final class CancellationToken
{
    private bool $cancelled = false;

    public function cancel() : void
    {
        $this->cancelled = true;
    }

    public function isCancelled() : bool
    {
        return $this->cancelled;
    }
}
