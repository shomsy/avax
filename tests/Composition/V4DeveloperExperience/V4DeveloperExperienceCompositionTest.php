<?php

declare(strict_types=1);

namespace Avax\Tests\Composition\V4DeveloperExperience;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * V4DeveloperExperienceCompositionTest — Verifies V4-04 architecture compliance.
 *
 * Folder says flow or capability.
 * File says responsibility.
 * Function says exact action.
 */
final class V4DeveloperExperienceCompositionTest extends TestCase
{
    private string $frameworkRoot;

    protected function setUp(): void
    {
        $this->frameworkRoot = dirname(__DIR__, 3).'/framework/System';
    }

    #[Test]
    public function doctorCapabilitiesFollowCanonicalShape(): void
    {
        $doctorPath = $this->frameworkRoot.'/Capabilities/Doctor';

        $expectedFiles = [
            'RunDoctor.php',
            'CheckAutoload.php',
            'CheckConfiguration.php',
            'CheckRuntimeMode.php',
            'CheckWarmSafety.php',
            'CheckMemoryGuard.php',
            'RegisterDoctorCommands.php',
        ];

        foreach ($expectedFiles as $file) {
            self::assertFileExists(
                $doctorPath.'/'.$file,
                "Doctor capability {$file} should exist",
            );
        }
    }

    #[Test]
    public function doctorFoundationTypesExist(): void
    {
        $foundationPath = $this->frameworkRoot.'/Capabilities/Doctor/Foundation';

        $expectedFiles = [
            'DoctorSeverity.php',
            'DoctorFinding.php',
            'DoctorReport.php',
        ];

        foreach ($expectedFiles as $file) {
            self::assertFileExists(
                $foundationPath.'/'.$file,
                "Doctor foundation type {$file} should exist",
            );
        }
    }

    #[Test]
    public function configurationCapabilitiesFollowCanonicalShape(): void
    {
        $configPath = $this->frameworkRoot.'/Capabilities/Configuration';

        self::assertFileExists(
            $configPath.'/RegisterConfigCommands.php',
            'Configuration commands should exist',
        );
    }

    #[Test]
    public function configurationFoundationTypesExist(): void
    {
        $foundationPath = $this->frameworkRoot.'/Configuration/Foundation';

        $expectedFiles = [
            'ApplicationConfiguration.php',
            'RuntimeConfiguration.php',
            'ConfigurationLoadFailed.php',
            'InvalidConfiguration.php',
        ];

        foreach ($expectedFiles as $file) {
            self::assertFileExists(
                $foundationPath.'/'.$file,
                "Configuration foundation type {$file} should exist",
            );
        }
    }

    #[Test]
    public function routingCapabilitiesFollowCanonicalShape(): void
    {
        $routingPath = $this->frameworkRoot.'/Capabilities/Routing';

        $expectedFiles = [
            'CacheRouteTable.php',
            'LoadCachedRoutes.php',
            'RegisterRouteCommands.php',
        ];

        foreach ($expectedFiles as $file) {
            self::assertFileExists(
                $routingPath.'/'.$file,
                "Routing capability {$file} should exist",
            );
        }
    }

    #[Test]
    public function configLoadersExist(): void
    {
        $configPath = $this->frameworkRoot.'/Configuration';

        $expectedFiles = [
            'LoadApplicationConfiguration.php',
            'LoadRuntimeConfiguration.php',
            'ValidateApplicationConfiguration.php',
            'ValidateRuntimeConfiguration.php',
        ];

        foreach ($expectedFiles as $file) {
            self::assertFileExists(
                $configPath.'/'.$file,
                "Configuration loader/validator {$file} should exist",
            );
        }
    }

    #[Test]
    public function noForbiddenFoldersInV4Dx(): void
    {
        $forbiddenPatterns = [
            '/Services\//',
            '/Helpers\//',
            '/Utils\//',
            '/Common\//',
            '/Shared\//',
            '/Managers\//',
        ];

        $dxPaths = [
            $this->frameworkRoot.'/Capabilities/Doctor',
            $this->frameworkRoot.'/Capabilities/Configuration',
            $this->frameworkRoot.'/Capabilities/Routing',
            $this->frameworkRoot.'/Configuration',
        ];

        foreach ($dxPaths as $path) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
            );

            foreach ($iterator as $file) {
                $relativePath = str_replace($path.'/', '', $file->getPathname());

                foreach ($forbiddenPatterns as $pattern) {
                    self::assertDoesNotMatchRegularExpression(
                        $pattern,
                        $relativePath,
                        "Forbidden folder pattern {$pattern} found in {$path}: {$relativePath}",
                    );
                }
            }
        }
    }

    #[Test]
    public function configFilesExist(): void
    {
        $projectRoot = dirname(__DIR__, 3);

        self::assertFileExists($projectRoot.'/config/app.php');
        self::assertFileExists($projectRoot.'/config/runtime.php');
    }

    #[Test]
    public function routeCachePlanExists(): void
    {
        $projectRoot = dirname(__DIR__, 3);

        self::assertFileExists($projectRoot.'/EVIDENCE/route-cache-plan.md');
    }
}
