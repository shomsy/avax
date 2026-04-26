<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Access\RiskBasedAccess\Support;

final class InMemoryRiskSignalStore implements RiskSignalStoreInterface
{
    /** @var list<RiskSignal> */
    private array $signals = [];

    public function record(RiskSignal $signal) : void
    {
        $this->signals[] = $signal;
    }

    public function forUser(int $userId) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->signals,
                                       callback: static fn (RiskSignal $signal) : bool => $signal->userId === $userId
                                   ));
    }
}
