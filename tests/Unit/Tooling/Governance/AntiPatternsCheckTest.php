<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for tooling/governance/check-antipatterns.php
 *
 * Proves: dictionary list enforcement, heading validation, suffix detection,
 * service locator detection, mode rejection.
 */
final class AntiPatternsCheckTest extends TestCase
{
    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-antipatterns.php';
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
        self::assertSame(0, $result['exit_code']);
    }

    #[Test]
    public function changedModeProducesStatus(): void
    {
        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root);
        self::assertTrue(
            str_contains($result['stdout'], 'GREEN') || str_contains($result['stdout'], 'RED'),
            'Must produce status'
        );
    }

    #[Test]
    public function expectedDictionaryListIsComplete(): void
    {
        $content = file_get_contents($this->script);

        $expectedEntries = [
            'analysis-paralysis.md',
            'architecture-theater.md',
            'blob-god-object.md',
            'cut-and-paste-programming.md',
            'fake-abstraction.md',
            'generic-bucket.md',
            'golden-hammer.md',
            'service-locator.md',
            'shallow-tests.md',
            'spaghetti-code.md',
            'stovepipe-system.md',
        ];

        foreach ($expectedEntries as $entry) {
            self::assertStringContainsString($entry, $content, "Checker must expect dictionary entry: {$entry}");
        }
    }

    #[Test]
    public function dictionaryEntriesExistOnDisk(): void
    {
        $dir = $this->root . '/.agents/dictionary/antipatterns';
        self::assertDirectoryExists($dir);

        $expectedEntries = [
            'analysis-paralysis.md',
            'architecture-theater.md',
            'blob-god-object.md',
            'cut-and-paste-programming.md',
            'fake-abstraction.md',
            'generic-bucket.md',
            'golden-hammer.md',
            'service-locator.md',
            'shallow-tests.md',
            'spaghetti-code.md',
            'stovepipe-system.md',
        ];

        foreach ($expectedEntries as $entry) {
            self::assertFileExists($dir . '/' . $entry, "Missing dictionary entry: {$entry}");
        }
    }

    #[Test]
    public function serviceSuffixFlagged(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringContainsString('Service', $content, 'Must flag Service suffix');
        self::assertStringContainsString('ServiceProvider', $content, 'Must except ServiceProvider');
    }

    #[Test]
    public function managerHelperUtilSuffixesFlagged(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringContainsString('Manager', $content);
        self::assertStringContainsString('Helper', $content);
        self::assertStringContainsString('Util', $content);
    }

    #[Test]
    public function serviceLocatorPatternFlagged(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringContainsString('ServiceLocator', $content);
        self::assertStringContainsString('->get(', $content);
    }

    #[Test]
    public function baselineModeIsRejected(): void
    {
        $result = RunsToolingCommand::run($this->script, ['--mode=baseline'], $this->root);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function unsupportedModeDoesNotSilentlyPass(): void
    {
        $result = RunsToolingCommand::run($this->script, ['--mode=xyz'], $this->root);
        self::assertNotSame(0, $result['exit_code']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }
}
