<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorFinding;
use Avax\Framework\System\Capabilities\Doctor\Foundation\DoctorReport;

final readonly class RunDoctor
{
    /**
     * @param  list<callable(): DoctorFinding>  $checks
     */
    public function __construct(
        private array $checks,
    ) {
    }

    public function run(): DoctorReport
    {
        $report = new DoctorReport();

        foreach ($this->checks as $check) {
            $report->add($check());
        }

        return $report;
    }
}
