<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RiskBasedAccess\EndpointPosture;

/**
 * Endpoint posture engine for risk-based access decisions.
 */
final readonly class EndpointPostureEngine
{
    /**
     * @param list<EndpointPostureSignalData> $signals
     * Evaluates posture signals and determines action.
     */
    public function evaluate(
        array                 $signals,
        EndpointPosturePolicy $policy
    ) : EndpointPostureDecision
    {
        $riskScore = $this->calculateRiskScore(signals: $signals);

        return match (true) {
            $riskScore >= $policy->denyThreshold       => EndpointPostureDecision::DENY,
            $riskScore >= $policy->stepUpThreshold     => EndpointPostureDecision::STEP_UP,
            $riskScore >= $policy->quarantineThreshold => EndpointPostureDecision::QUARANTINE,
            default                                    => EndpointPostureDecision::ALLOW,
        };
    }

    /**
     * @param list<EndpointPostureSignalData> $signals
     * Calculates aggregate risk score from signals.
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

/**
 * Policy thresholds for endpoint posture decisions.
 */
final readonly class EndpointPosturePolicy
{
    public float $stepUpThreshold;
    public float $denyThreshold;

    public function __construct(
        float|null   $denyThreshold = null,
        float|null   $stepUpThreshold = null,
        public float $quarantineThreshold = 0.3
    )
    {
        $denyThreshold         ??= 0.8;
        $stepUpThreshold       ??= 0.5;
        $this->denyThreshold   = $denyThreshold;
        $this->stepUpThreshold = $stepUpThreshold;
    }
}

/**
 * Endpoint posture decision outcomes.
 */
enum EndpointPostureDecision: string
{
    case ALLOW      = 'allow';
    case STEP_UP    = 'step_up';
    case DENY       = 'deny';
    case QUARANTINE = 'quarantine';
}
