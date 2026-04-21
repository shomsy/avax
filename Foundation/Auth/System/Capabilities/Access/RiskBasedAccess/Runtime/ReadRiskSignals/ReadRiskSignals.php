<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals;

use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class ReadRiskSignals
{
    private DeterministicRiskEngine $riskEngine;
    private CurrentAuthentication   $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        DeterministicRiskEngine                     $riskEngine
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->riskEngine            = $riskEngine;
    }

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
