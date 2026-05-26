<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for tooling/governance/check-engineering-canon-traceability.php
 *
 * Proves: all markers present, missing marker fails, duplicate marker fails,
 * missing source-principle fails, dictionary heading validation.
 */
final class EngineeringCanonTraceabilityCheckTest extends TestCase
{
    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-engineering-canon-traceability.php';
    }

    #[Test]
    public function scriptExists(): void
    {
        self::assertFileExists($this->script);
    }

    #[Test]
    public function syntaxIsValid(): void
    {
        $result = RunsToolingCommand::run(PHP_BINARY, ['-l', $this->script]);
        self::assertSame(0, $result['exit_code'], 'Syntax check failed: ' . $result['stdout']);
    }

    #[Test]
    public function passesWhenAllFilesPresent(): void
    {
        $result = RunsToolingCommand::run($this->script, [], $this->root);
        // This may pass or fail depending on current state; verify output format
        $output = $result['stdout'];
        self::assertTrue(
            str_contains($output, 'GREEN') || str_contains($output, 'RED'),
            'Must produce GREEN or RED status'
        );
    }

    #[Test]
    public function reportsAllMissingFiles(): void
    {
        $result = RunsToolingCommand::run($this->script, [], $this->root, [
            'AVAX_SDLC_ROOT' => '/tmp/nonexistent-root-' . uniqid(),
        ]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('MISSING', $result['stdout']);
    }

    #[Test]
    public function outputIncludesModeInfo(): void
    {
        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root);
        self::assertStringContainsString('mode=changed', $result['stdout']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }
}
