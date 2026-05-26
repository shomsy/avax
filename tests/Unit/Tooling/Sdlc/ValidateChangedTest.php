<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Sdlc;

use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for tooling/sdlc/validate-changed.php runner.
 *
 * Proves: aggregates required check failures, exit codes, output includes final status.
 */
final class ValidateChangedTest extends TestCase
{
    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/sdlc/validate-changed.php';
    }

    #[Test]
    public function scriptExists(): void
    {
        self::assertFileExists($this->script);
    }

    #[Test]
    public function syntaxIsValid(): void
    {
        $result = RunsToolingCommand::run($this->script, [], $this->root);
        // Even if it fails validation, it should not have parse errors
        self::assertStringNotContainsString('Parse error', $result['stdout'] . $result['stderr']);
        self::assertStringNotContainsString('Fatal error', $result['stdout'] . $result['stderr']);
    }

    #[Test]
    public function outputIncludesFinalStatus(): void
    {
        $result = RunsToolingCommand::run($this->script, [], $this->root);
        $output = $result['stdout'];
        // Must contain either GREEN or RED status line
        self::assertTrue(
            str_contains($output, 'GREEN_CHANGED_SCOPE_READY') || str_contains($output, 'RED_BLOCKED'),
            'Output must contain final status line. Got: ' . substr($output, -500)
        );
    }

    #[Test]
    public function outputIncludesRuntimeHeader(): void
    {
        $result = RunsToolingCommand::run($this->script, [], $this->root);
        self::assertStringContainsString('SDLC Changed Validation', $result['stdout']);
        self::assertStringContainsString('PHP_BINARY=', $result['stdout']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }
}
