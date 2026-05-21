<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RiskBasedAccess\EndpointPosture;

/**
 * Endpoint posture engine for risk-based access decisions.
 */
final readonly class EndpointPostureEngine
{
    /**
     * @param list<EndpointPostureSignalData> $signals
     */
    public function evaluate(
        array                 $signals,
        EndpointPosturePolicy $endpointPosturePolicy,
    ) : EndpointPostureDecision
    {
        $riskScore = $this->calculateRiskScore(signals: $signals);

        return match (true) {
            $riskScore >= $endpointPosturePolicy->denyThreshold       => EndpointPostureDecision::DENY,
            $riskScore >= $endpointPosturePolicy->stepUpThreshold     => EndpointPostureDecision::STEP_UP,
            $riskScore >= $endpointPosturePolicy->quarantineThreshold => EndpointPostureDecision::QUARANTINE,
            default                                                   => EndpointPostureDecision::ALLOW,
        };
    }

    /**
     * @param list<EndpointPostureSignalData> $signals
     */
    private function calculateRiskScore(array $signals) : float
    {
        if ($signals === []) {
            return 0.0;
        }

        $totalScore = 0.0;
        $weightSum  = 0.0;

        foreach ($signals as $signal) {
            $weight = match ($signal->type) {
                EndpointPostureSignal::IP_REPUTATION                                                  => 0.3,
                EndpointPostureSignal::IMPOSSIBLE_TRAVEL                                              => 0.4,
                EndpointPostureSignal::VPN_DETECTION, EndpointPostureSignal::PROXY_DETECTION          => 0.25,
                EndpointPostureSignal::GEO_VELOCITY                                                   => 0.2,
                EndpointPostureSignal::ASN_REPUTATION                                                 => 0.15,
                EndpointPostureSignal::DEVICE_FINGERPRINT, EndpointPostureSignal::BROWSER_FINGERPRINT => 0.1,
            };

            $totalScore += $signal->score * $weight;
            $weightSum  += $weight;
        }

        return $weightSum > 0 ? $totalScore / $weightSum : 0.0;
    }
}
