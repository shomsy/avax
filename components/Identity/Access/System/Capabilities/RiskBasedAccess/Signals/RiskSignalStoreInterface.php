<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals;

interface RiskSignalStoreInterface
{
    public function record(RiskSignal $signal): void;

    /**
     * @return list<RiskSignal>
     */
    public function forUser(int $userId): array;
}
