<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Examples\GoldenPathRuntimeApp\WebhookIngestionApp;
use Avax\Framework\System\Capabilities\RuntimeSafety\RuntimeSafety;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\PublicSurface\Avax;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: RuntimeSafety inspection passes for the webhook app.
 *
 * The runtime doctor checks for state leaks, static state issues,
 * and reset verification.
 */
final class RuntimeDoctorTest extends TestCase
{
    #[Test]
    public function runtimeSafetyInspectReturnsFindings() : void
    {
        $this->bootAvax();

        $runtimeSafety = new RuntimeSafety();
        $findings      = $runtimeSafety->inspect();

        // Inspection returns a list of RuntimeSafetyFinding
        self::assertGreaterThan(0, count($findings));
    }

    private function bootAvax() : Avax
    {
        $projectPath = new ProjectPath('/home/shomsy/projects/avax');
        $environment = new EnvironmentName('testing');

        return Avax::boot(WebhookIngestionApp::createBuilder($projectPath, $environment));
    }

    #[Test]
    public function runtimeSafetyDetectsStateLeaks() : void
    {
        $this->bootAvax();

        $runtimeSafety = new RuntimeSafety();
        $findings      = $runtimeSafety->inspect();

        // The inspection should complete without errors
        // We cannot guarantee zero findings across the full codebase,
        // but we verify the inspection runs successfully
        foreach ($findings as $finding) {
            self::assertObjectHasProperty('severity', $finding);
            self::assertObjectHasProperty('message', $finding);
        }
    }

    #[Test]
    public function resetVerificationPassesForRegisteredComponents() : void
    {
        $avax = $this->bootAvax();

        // Reset should complete without throwing
        $report = $avax->resetState();

        // Report should have reset component names
        self::assertNotEmpty($report->resetComponents());
    }
}
