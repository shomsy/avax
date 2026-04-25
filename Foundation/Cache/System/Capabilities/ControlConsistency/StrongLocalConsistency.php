<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ControlConsistency;

final readonly class StrongLocalConsistency
{
    public function isStrongConsistencyGuaranteed() : bool
    {
        return true;
    }

    public function requiresSynchronousWrite() : bool
    {
        return true;
    }
}