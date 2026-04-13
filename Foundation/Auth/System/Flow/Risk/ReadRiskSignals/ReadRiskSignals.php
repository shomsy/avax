<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Risk\ReadRiskSignals;

use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\Risk\RiskSignal;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;

final readonly class ReadRiskSignals
{
    public function __construct(
        #[\SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private DeterministicRiskEngine                      $riskEngine
    ) {}

    /**
     * @return list<RiskSignal>
     */
    public function execute(int|null $userId = null) : array
    {
        $resolvedUserId = $userId ?? $this->currentAuthentication->read()->user()?->id;

        if ($resolvedUserId === null) {
            return [];
        }

        return $this->riskEngine->readSignalsForUser(userId: $resolvedUserId);
    }
}
