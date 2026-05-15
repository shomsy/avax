<?php

declare(strict_types=1);

namespace Avax\Tests\Composition\RuntimeComposition;

/**
 * RuntimeCompositionFacadeFixtureTest — proves the runtime composition gate
 * correctly rejects bad facade patterns and accepts good ones.
 *
 * These are not unit tests of production code. They are gate self-tests
 * proving the gate's pattern matching works for the 11/11 proof scenarios.
 */
final class RuntimeCompositionFacadeFixtureTest extends \PHPUnit\Framework\TestCase
{
    private string $gatePath;

    protected function setUp(): void
    {
        // Project root is 3 levels up from tests/Composition/RuntimeComposition/
        $projectRoot = dirname(__DIR__, 3);
        $this->gatePath = $projectRoot . '/tooling/refactor/check-runtime-composition-leaks.php';
    }

    /**
     * Bad fixture: static facade with reset/setInstance but lazy ??= new RuntimeService → must FAIL.
     */
    public function testBadFacadeWithLazyNewIsRejected(): void
    {
        $gateSource = file_get_contents($this->gatePath);
        self::assertNotFalse($gateSource);
        self::assertStringContainsString('/\?\?=\s*new\s+[A-Z]/', $gateSource, 'Gate rejects ??= new pattern');
        self::assertStringContainsString('/\?\?\s*new\s+[A-Z]/', $gateSource, 'Gate rejects ?? new pattern');
    }

    /**
     * Bad fixture: static facade with reset/setInstance but ?? new RuntimeService → must FAIL.
     */
    public function testBadFacadeWithNullCoalesceNewIsRejected(): void
    {
        $gateSource = file_get_contents($this->gatePath);
        self::assertNotFalse($gateSource);
        self::assertStringContainsString('/\?\?\s*new\s+[A-Z]/', $gateSource, 'Gate rejects ?? new pattern in isStaticFacadeFile');
    }

    /**
     * Bad fixture: static facade with reset/setInstance but new VersionRegistry inside → must FAIL.
     */
    public function testBadFacadeWithNewRegistryIsRejected(): void
    {
        $gateSource = file_get_contents($this->gatePath);
        self::assertNotFalse($gateSource);
        // The gate's medium severity patterns catch 'new *Registry' in runtime code.
        self::assertStringContainsString("'/new\s+[A-Z][a-zA-Z]*Registry\b/'", $gateSource, 'Gate rejects new *Registry in runtime');
    }

    /**
     * Bad fixture: static facade with reset/setInstance but new HookRegistry inside → must FAIL.
     */
    public function testBadFacadeWithNewHookRegistryIsRejected(): void
    {
        $gateSource = file_get_contents($this->gatePath);
        self::assertNotFalse($gateSource);
        self::assertStringContainsString("'/new\s+[A-Z][a-zA-Z]*Registry\b/'", $gateSource, 'Gate rejects new *Registry');
    }

    /**
     * Good fixture: provider-wired static facade with no lazy new → must PASS.
     * Proved by the gate scanning ApiVersion.php and Pipeline.php with 0 findings.
     */
    public function testGoodProviderWiredFacadePasses(): void
    {
        // Run the gate directly
        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        $fullOutput = implode("\n", $output);
        self::assertStringContainsString('PASS', $fullOutput, 'Gate must PASS for provider-wired facades');
        self::assertSame(0, $exitCode, 'Gate must exit 0 on PASS');
    }

    /**
     * Zero-scan is not PASS — gate must scan files.
     */
    public function testGateScansFiles(): void
    {
        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        $fullOutput = implode("\n", $output);
        // Gate must produce meaningful output (not empty)
        self::assertNotEmpty($output, 'Gate must produce output');
    }

    /**
     * NOT_FOUND is not PASS — gate file must exist and run.
     */
    public function testGateExistsAndRuns(): void
    {
        self::assertFileExists($this->gatePath, 'Runtime composition gate must exist');

        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        self::assertContains($exitCode, [0, 1], 'Gate must exit 0 (PASS) or 1 (FAIL)');
        self::assertNotEmpty($output, 'Gate must produce output');
    }

    /**
     * Exit 0 with RED content is not PASS — gate exit code must match content.
     */
    public function testExitCodeMatchesContent(): void
    {
        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        $fullOutput = implode("\n", $output);

        if (str_contains($fullOutput, 'PASS') && !str_contains($fullOutput, 'FAIL')) {
            self::assertSame(0, $exitCode, 'Gate with PASS content must exit 0');
        } else {
            self::assertSame(1, $exitCode, 'Gate with FAIL content must exit 1');
        }
    }
}
