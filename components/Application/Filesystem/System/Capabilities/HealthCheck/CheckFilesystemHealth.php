<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\HealthCheck;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthFinding;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthReport;
use Avax\Framework\System\Capabilities\Health\Foundation\HealthStatus;
use Throwable;

/**
 * Health check for the Filesystem component.
 *
 * Probes: temp directory writability, root path accessibility.
 * Returns canonical HealthReport.
 */
final class CheckFilesystemHealth
{
    public function __construct(
        private readonly Filesystem  $filesystem,
        private readonly string|null $rootPath = null,
    ) {}

    public function check() : HealthReport
    {
        $findings = [];
        $overall  = HealthStatus::Green;

        $fs = $this->filesystem;

        // 1. Temp directory is writable
        $tempDir      = sys_get_temp_dir();
        $tempWritable = $this->checkTempWritable($fs, $tempDir, $findings);
        if (! $tempWritable) {
            $overall = HealthStatus::Red;
        }

        // 2. Root path accessibility (if configured)
        $rootPath = $this->rootPath;
        if ($rootPath !== '' && $rootPath !== null) {
            $this->checkRootAccessible($fs, $rootPath, $findings, $overall);
        }

        return new HealthReport(findings: $findings, overall: $overall);
    }

    /**
     * @param list<HealthFinding> $findings
     */
    private function checkTempWritable(Filesystem $fs, string $tempDir, array &$findings) : bool
    {
        try {
            if (! is_writable($tempDir)) {
                $findings[] = new HealthFinding('filesystem.temp', HealthStatus::Red, sprintf('Temp directory %s is not writable', $tempDir));

                return false;
            }

            // Probe: can we actually write and read back?
            $testFile = $tempDir . '/avax_health_' . uniqid('', true) . '.tmp';
            $fs->write($testFile, 'ok');

            if (! $fs->isReadable($testFile)) {
                $findings[] = new HealthFinding('filesystem.temp', HealthStatus::Red, 'Temp directory writable but not readable');

                return false;
            }

            $fs->delete($testFile);

            $findings[] = new HealthFinding('filesystem.temp', HealthStatus::Green, 'Temp directory is writable');

            return true;
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('filesystem.temp', HealthStatus::Red, sprintf('Temp check failed: %s', $e->getMessage()));

            return false;
        }
    }

    /**
     * @param list<HealthFinding> $findings
     */
    private function checkRootAccessible(Filesystem $fs, string $rootPath, array &$findings, HealthStatus &$overall) : void
    {
        try {
            if (! $fs->exists($rootPath)) {
                $findings[] = new HealthFinding('filesystem.root', HealthStatus::Yellow, sprintf('Root path %s does not exist', $rootPath));
                if ($overall === HealthStatus::Green) {
                    $overall = HealthStatus::Yellow;
                }

                return;
            }

            if (! $fs->isReadable($rootPath)) {
                $findings[] = new HealthFinding('filesystem.root', HealthStatus::Red, sprintf('Root path %s is not readable', $rootPath));
                $overall    = HealthStatus::Red;

                return;
            }

            $findings[] = new HealthFinding('filesystem.root', HealthStatus::Green, 'Root path is accessible');
        } catch (Throwable $e) {
            $findings[] = new HealthFinding('filesystem.root', HealthStatus::Yellow, sprintf('Root check failed: %s', $e->getMessage()));
            if ($overall === HealthStatus::Green) {
                $overall = HealthStatus::Yellow;
            }
        }
    }
}
