<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor\Foundation;

/**
 * DoctorReport — Aggregates all doctor findings and provides overall status.
 */
final class DoctorReport
{
    /**
     * @var list<DoctorFinding>
     */
    private array $findings = [];

    public function add(DoctorFinding $finding): void
    {
        $this->findings[] = $finding;
    }

    /**
     * @return list<DoctorFinding>
     */
    public function findings(): array
    {
        return $this->findings;
    }

    public function overallStatus(): DoctorSeverity
    {
        foreach ($this->findings as $finding) {
            if ($finding->severity === DoctorSeverity::Red) {
                return DoctorSeverity::Red;
            }
        }

        foreach ($this->findings as $finding) {
            if ($finding->severity === DoctorSeverity::Yellow) {
                return DoctorSeverity::Yellow;
            }
        }

        foreach ($this->findings as $finding) {
            if ($finding->severity === DoctorSeverity::Unknown) {
                return DoctorSeverity::Unknown;
            }
        }

        return DoctorSeverity::Green;
    }

    public function render(): string
    {
        $lines = ["Avax Doctor\n", ''];

        foreach ($this->findings as $finding) {
            $symbol = match ($finding->severity) {
                DoctorSeverity::Green => '✓',
                DoctorSeverity::Yellow => '!',
                DoctorSeverity::Red => '✗',
                DoctorSeverity::Unknown => '?',
            };
            $lines[] = "  [{$symbol}] {$finding->check}: {$finding->message}";
        }

        $overall = $this->overallStatus();
        $lines[] = '';
        $lines[] = "Overall: {$overall->value}";

        return implode("\n", $lines);
    }
}
