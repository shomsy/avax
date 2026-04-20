<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Risk\ReadRiskSignals;

use Avax\Auth\System\Capabilities\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Risk\RiskSignal;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
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
