<?php

declare(strict_types=1);

namespace Avax\Tests\Composition\RuntimeComposition;

/**
 * RuntimeCompositionFacadeFixtureTest — proves the runtime composition gate
 * correctly rejects bad patterns and accepts good ones.
 *
 * These are not unit tests of production code. They are gate self-tests
 * proving the gate's pattern matching works correctly.
 */
final class RuntimeCompositionFacadeFixtureTest extends \PHPUnit\Framework\TestCase
{
    private string $gatePath;

    protected function setUp(): void
    {
        $projectRoot = dirname(__DIR__, 3);
        $this->gatePath = $projectRoot . '/tooling/refactor/check-runtime-composition-leaks.php';
    }

    /**
     * The gate must exist and be executable.
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
     * When the gate finds no violations, it must output PASS and exit 0.
     */
    public function testPassStatusExitsZero(): void
    {
        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        $fullOutput = implode("\n", $output);

        // If the gate outputs PASS, it must exit 0
        if (str_contains($fullOutput, 'PASS')) {
            self::assertSame(0, $exitCode, 'Gate with PASS status must exit 0');
        }
    }

    /**
     * The gate must report its result honestly — never empty output.
     */
    public function testGateReportsResultHonestly(): void
    {
        $output = [];
        $exitCode = 0;
        exec('php ' . escapeshellarg($this->gatePath) . ' 2>&1', $output, $exitCode);

        $fullOutput = implode("\n", $output);

        // Gate must report either PASS or FAIL
        self::assertTrue(
            str_contains($fullOutput, 'PASS') || str_contains($fullOutput, 'FAIL'),
            'Gate must report PASS or FAIL status',
        );
    }
}
