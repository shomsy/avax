<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Types\DoctorReport;

final readonly class ExecuteDoctorChecks
{
    /**
     * @param list<callable(): DoctorFinding> $checks
     */
    public function __construct(
        private array $checks,
    ) {}

    public function run() : DoctorReport
    {
        $report = new DoctorReport();

        foreach ($this->checks as $check) {
            $report->add($check());
        }

        return $report;
    }
}
