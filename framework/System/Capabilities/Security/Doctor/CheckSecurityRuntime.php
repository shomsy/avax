<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorSeverity;

final readonly class CheckSecurityRuntime
{
    public function __construct(
        private string $signingSecret = '',
        private bool $policyEngineAvailable = true,
        private bool $featureFlagsAvailable = true,
        private bool $serviceDiscoveryAvailable = true,
        private bool $redactionAvailable = true,
    ) {
    }

    /** @return list<DoctorFinding> */
    public function check(): array
    {
        $findings = [];

        if ($this->signingSecret !== '') {
            $findings[] = new DoctorFinding(
                'Request Signing',
                DoctorSeverity::Green,
                'HMAC request signing configured.',
            );
        } else {
            $findings[] = new DoctorFinding(
                'Request Signing',
                DoctorSeverity::Yellow,
                'Request signing available but no signing secret configured.',
            );
        }

        $findings[] = new DoctorFinding(
            'Policy Engine',
            $this->policyEngineAvailable ? DoctorSeverity::Green : DoctorSeverity::Red,
            $this->policyEngineAvailable
                ? 'Policy engine available with default-deny semantics.'
                : 'Policy engine not available.',
        );

        $findings[] = new DoctorFinding(
            'Feature Flags',
            $this->featureFlagsAvailable ? DoctorSeverity::Green : DoctorSeverity::Yellow,
            $this->featureFlagsAvailable
                ? 'Feature flags runtime available.'
                : 'Feature flags not available.',
        );

        $findings[] = new DoctorFinding(
            'Service Discovery',
            $this->serviceDiscoveryAvailable ? DoctorSeverity::Green : DoctorSeverity::Yellow,
            $this->serviceDiscoveryAvailable
                ? 'Service discovery registry available.'
                : 'Service discovery not available.',
        );

        $findings[] = new DoctorFinding(
            'Secret Redaction',
            $this->redactionAvailable ? DoctorSeverity::Green : DoctorSeverity::Yellow,
            $this->redactionAvailable
                ? 'Secret redaction available for security output.'
                : 'Secret redaction not available.',
        );

        return $findings;
    }
}
