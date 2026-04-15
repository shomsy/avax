<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Risk;

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
        return array_values(array_filter(
                                $this->signals,
                                static fn (RiskSignal $signal) : bool => $signal->userId === $userId
                            ));
    }
}
