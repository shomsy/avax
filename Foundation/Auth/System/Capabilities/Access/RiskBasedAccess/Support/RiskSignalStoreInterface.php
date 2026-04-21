<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

interface RiskSignalStoreInterface
{
    public function record(RiskSignal $signal) : void;

    /**
     * @return list<RiskSignal>
     */
    public function forUser(int $userId) : array;
}
