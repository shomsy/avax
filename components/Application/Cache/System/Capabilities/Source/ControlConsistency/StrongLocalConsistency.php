<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ControlConsistency;

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