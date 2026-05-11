<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Doctor;

use Avax\Framework\System\Capabilities\Doctor\Types\DoctorSeverity;
use Closure;

/**
 * RegisterDoctorCommands — provides CLI command closures for doctor/validate/inspect.
 */
final readonly class RegisterDoctorCommands
{
    /**
     * @return array<string, Closure>
     */
    public function __invoke(): array
    {
        return [
            'doctor' => $this->doctorCommand(),
            'validate' => $this->validateCommand(),
            'inspect' => $this->inspectCommand(),
        ];
    }

    private function doctorCommand(): Closure
    {
        return static function (array $args): string {
            $workerMode = in_array('--worker', $args, true);

            $checks = [
                new CheckAutoload(),
                new CheckConfiguration(),
                new CheckRuntimeMode(),
                new CheckWarmSafety(),
                new CheckMemoryGuard(),
            ];

            $executeChecks = new ExecuteDoctorChecks(checks: $checks);
            $report        = $executeChecks->run();

            $output = "\033[33mAvax Doctor\033[0m\n";
            $output .= sprintf("Mode: %s\n\n", $workerMode ? 'worker' : 'development');
            $output .= $report->render();

            $status = $report->overallStatus();

            if ($status === DoctorSeverity::Green) {
                $output .= "\n\033[32mDoctor check passed.\033[0m\n";
            } else {
                $output .= "\n\033[31mDoctor check failed.\033[0m\n";
            }

            return $output;
        };
    }

    private function validateCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mAvax Validate\033[0m\n\n";

            $full = in_array('--full', $args, true);

            if ($full) {
                $output .= "Running full validation...\n\n";

                $checks = [
                    new CheckAutoload(),
                    new CheckConfiguration(),
                    new CheckRuntimeMode(),
                    new CheckWarmSafety(),
                    new CheckMemoryGuard(),
                ];

                $executeChecks = new ExecuteDoctorChecks(checks: $checks);
                $report        = $executeChecks->run();
                $output .= $report->render();
            } else {
                $output .= "Running focused validation...\n\n";

                $checks = [
                    new CheckConfiguration(),
                    new CheckRuntimeMode(),
                ];

                $executeChecks = new ExecuteDoctorChecks(checks: $checks);
                $report        = $executeChecks->run();
                $output .= $report->render();
            }

            $status = $report->overallStatus();

            if ($status === DoctorSeverity::Green) {
                $output .= "\n\033[32mValidation passed.\033[0m\n";
            } else {
                $output .= "\n\033[31mValidation failed.\033[0m\n";
            }

            return $output;
        };
    }

    private function inspectCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mAvax Inspect\033[0m\n\n";

            $checks = [
                new CheckAutoload(),
                new CheckConfiguration(),
                new CheckRuntimeMode(),
                new CheckWarmSafety(),
                new CheckMemoryGuard(),
            ];

            $executeChecks = new ExecuteDoctorChecks(checks: $checks);
            $report        = $executeChecks->run();
            $output .= $report->render();

            return $output;
        };
    }
}
