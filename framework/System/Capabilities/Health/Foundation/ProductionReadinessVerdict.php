<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Health\Foundation;

final readonly class ProductionReadinessVerdict
{
    public function __construct(
        public HealthStatus $status,
        /** @var list<HealthFinding> $findings */
        public array $findings = [],
        public int $passedCount = 0,
        public int $failedCount = 0,
        public int $unknownCount = 0,
        public string $summary = '',
    ) {
    }

    /**
     * @param list<HealthFinding> $findings
     */
    public static function fromFindings(array $findings, string $version = ''): self
    {
        $passed = 0;
        $failed = 0;
        $unknown = 0;
        $overall = HealthStatus::Green;

        foreach ($findings as $finding) {
            if ($finding->status === HealthStatus::Green || $finding->status === HealthStatus::Yellow) {
                $passed++;
            } elseif ($finding->status === HealthStatus::Red) {
                $failed++;
            } elseif ($finding->status === HealthStatus::Unknown) {
                $unknown++;
            }

            if ($finding->status === HealthStatus::Red) {
                $overall = HealthStatus::Red;
            } elseif ($finding->status === HealthStatus::Yellow && $overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            } elseif ($finding->status === HealthStatus::Unknown && $overall === HealthStatus::Green) {
                $overall = HealthStatus::Unknown;
            }
        }

        return new self(
            $overall,
            $findings,
            $passed,
            $failed,
            $unknown,
            "Passed: {$passed}, Failed: {$failed}, Unknown: {$unknown}. Version: {$version}.",
        );
    }
}
