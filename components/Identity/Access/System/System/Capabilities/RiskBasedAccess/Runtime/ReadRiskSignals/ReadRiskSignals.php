<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Runtime\ReadRiskSignals;

use Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals\DeterministicRiskEngine;
use Avax\Components\Identity\Access\System\System\Capabilities\RiskBasedAccess\Signals\RiskSignal;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use SensitiveParameter;

final readonly class ReadRiskSignals
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication   $currentAuthentication,
        private DeterministicRiskEngine $deterministicRiskEngine,
    ) {}

    /**
     * @return list<RiskSignal>
     */
    public function execute(?int $userId = null) : array
    {
        $resolvedUserId = $userId ?? $this->currentAuthentication->read()->user()?->id;

        if ($resolvedUserId === null) {
            return [];
        }

        return $this->deterministicRiskEngine->readSignalsForUser(userId: $resolvedUserId);
    }
}
