<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Risk\System\PublicSurface;

use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskAction;
use Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\Signals\RiskDecision;

/**
 * Risk — risk assessment and signals.
 */
final class Risk
{
    public function assessCurrent(string|null $ipAddress = null, string|null $userAgent = null) : RiskDecision|null
    {
        if ($ipAddress === null && $userAgent === null) {
            return null;
        }

        $signals = [];

        if ($ipAddress !== null) {
            $signals[] = 'ip:' . $ipAddress;
        }

        if ($userAgent !== null) {
            $signals[] = 'ua:' . $userAgent;
        }

        return new RiskDecision(
            action : RiskAction::ALLOW,
            reasons: $signals,
        );
    }

    /**
     * @return list<string>
     */
    public function signals(int|null $userId = null) : array
    {
        if ($userId === null) {
            return [];
        }

        return [];
    }

    public function endpointPosture() : EndpointPosture
    {
        return new EndpointPosture();
    }
}
