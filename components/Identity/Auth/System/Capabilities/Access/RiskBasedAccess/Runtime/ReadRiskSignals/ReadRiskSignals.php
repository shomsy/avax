<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Runtime\ReadRiskSignals;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskSignal;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class ReadRiskSignals
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication   $currentAuthentication,
        private DeterministicRiskEngine $riskEngine,
    ) {}

    /**
     * @return list<RiskSignal>
     */
    public function execute(int $userId = null) : array
    {
        $resolvedUserId = $userId ?? $this->currentAuthentication->read()->user()?->id;

        if ($resolvedUserId === null) {
            return [];
        }

        return $this->riskEngine->readSignalsForUser(userId: $resolvedUserId);
    }
}
