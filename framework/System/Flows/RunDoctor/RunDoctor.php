<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\RunDoctor;

use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafetyFinding;

final readonly class RunDoctor
{
    public function __construct(
        private RuntimeSafety $runtimeSafety = new RuntimeSafety(),
    ) {}

    public function handle(bool $workerMode = false) : int
    {
        echo "\033[33mAvax Runtime Doctor\033[0m\n";
        echo sprintf("Mode: %s\n\n", $workerMode ? 'worker' : 'development');

        $findings = $this->runtimeSafety->inspect();

        if ($findings === []) {
            echo "\033[32mNo runtime safety issues detected.\033[0m\n";

            if ($workerMode) {
                echo "\033[32mApplication is safe for long-lived workers.\033[0m\n";
            }

            return 0;
        }

        $criticalCount = 0;
        $warningCount = 0;
        $infoCount = 0;

        foreach ($findings as $finding) {
            match ($finding->severity) {
                RuntimeSafetyFinding::SEVERITY_CRITICAL => $criticalCount++,
                RuntimeSafetyFinding::SEVERITY_WARNING => $warningCount++,
                default                                => $infoCount++,
            };
        }

        foreach ($findings as $finding) {
            $this->printFinding($finding);
        }

        echo "\n";
        echo "Summary:\n";
        echo sprintf(
            "  \033[31m%d critical\033[0m, \033[33m%d warnings\033[0m, \033[36m%d info\033[0m\n",
            $criticalCount,
            $warningCount,
            $infoCount,
        );

        if ($workerMode) {
            if ($this->runtimeSafety->isWorkerSafe()) {
                echo "\033[32mApplication is safe for long-lived workers.\033[0m\n";

                return 0;
            }

            echo "\033[31mApplication is NOT safe for long-lived workers.\033[0m\n";
            echo "Fix critical issues before deploying to FrankenPHP, RoadRunner, or Swoole.\n";

            return 1;
        }

        return $criticalCount > 0 ? 1 : 0;
    }

    private function printFinding(RuntimeSafetyFinding $runtimeSafetyFinding) : void
    {
        $color = match ($runtimeSafetyFinding->severity) {
            RuntimeSafetyFinding::SEVERITY_CRITICAL => '31',
            RuntimeSafetyFinding::SEVERITY_WARNING => '33',
            default                                => '36',
        };

        echo sprintf(
            "\033[%sm[%s] %s\033[0m\n",
            $color,
            strtoupper($runtimeSafetyFinding->severity),
            $runtimeSafetyFinding->message,
        );

        echo sprintf("  Component: %s\n", $runtimeSafetyFinding->component);

        if ($runtimeSafetyFinding->location !== null) {
            echo sprintf("  Location: %s\n", $runtimeSafetyFinding->location);
        }

        if ($runtimeSafetyFinding->remediation !== null) {
            echo sprintf("  Fix: %s\n", $runtimeSafetyFinding->remediation);
        }

        echo "\n";
    }
}
