<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals;

final class InMemoryRiskSignalStore implements RiskSignalStoreInterface
{
    /** @var list<RiskSignal> */
    private array $signals = [];

    public function record(RiskSignal $riskSignal) : void
    {
        $this->signals[] = $riskSignal;
    }

    public function forUser(int $userId) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->signals,
                                       callback: static fn (RiskSignal $riskSignal) : bool => $riskSignal->userId === $userId,
                                   ));
    }
}
