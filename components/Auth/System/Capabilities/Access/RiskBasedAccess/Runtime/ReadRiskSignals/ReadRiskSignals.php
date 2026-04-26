<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals;

use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class ReadRiskSignals
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private DeterministicRiskEngine                     $riskEngine
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
