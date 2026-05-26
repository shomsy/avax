<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Sdlc;

use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for tooling/sdlc/SdlcRuntime.php
 *
 * Proves: PHP binary detection, PHP version detection, git discovery,
 * git unavailability handling, runtime diagnostics, root override.
 */
final class SdlcRuntimeTest extends TestCase
{
    private string $script;

    protected function setUp(): void
    {
        parent::setUp();
        $this->script = dirname(__DIR__, 4) . '/tooling/sdlc/SdlcRuntime.php';
        self::assertFileExists($this->script, 'SdlcRuntime.php must exist');
    }

    #[Test]
    public function phpBinaryReturnsNonEmptyString(): void
    {
        // We test by including the class in a subprocess that prints PHP_BINARY
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        echo SdlcRuntime::phpBinary();
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        self::assertNotEmpty(trim($result['stdout']), 'phpBinary() must return non-empty string');
    }

    #[Test]
    public function phpVersionMatchesRunningVersion(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        echo SdlcRuntime::phpVersion();
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        self::assertSame(PHP_VERSION, trim($result['stdout']));
    }

    #[Test]
    public function findGitReturnsPathWhenGitExists(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        $git = SdlcRuntime::findGit();
        echo $git ?? 'NULL';
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        $output = trim($result['stdout']);
        self::assertNotSame('NULL', $output, 'findGit() should find git on CI/dev machines');
        self::assertStringContainsString('git', $output);
    }

    #[Test]
    public function findGitReturnsNullWhenDisabled(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        $git = SdlcRuntime::findGit();
        echo $git ?? 'NULL';
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script], null, [
            'AVAX_SDLC_DISABLE_GIT' => '1',
        ]);
        self::assertSame(0, $result['exit_code']);
        self::assertSame('NULL', trim($result['stdout']));
    }

    #[Test]
    public function requireGitExitsWithErrorWhenGitDisabled(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        SdlcRuntime::requireGit();
        echo 'SHOULD_NOT_REACH';
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script], null, [
            'AVAX_SDLC_DISABLE_GIT' => '1',
        ]);
        self::assertNotSame(0, $result['exit_code'], 'requireGit() must exit non-zero when git unavailable');
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringNotContainsString('SHOULD_NOT_REACH', $result['stdout']);
    }

    #[Test]
    public function printRuntimeHeaderIncludesDiagnostics(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        SdlcRuntime::printRuntimeHeader('Test Header');
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        self::assertStringContainsString('Test Header', $result['stdout']);
        self::assertStringContainsString('PHP_BINARY=', $result['stdout']);
        self::assertStringContainsString('PHP_VERSION=', $result['stdout']);
        self::assertStringContainsString('git=', $result['stdout']);
    }

    #[Test]
    public function rootOverrideWorksViaEnvVar(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        echo SdlcRuntime::root();
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script], null, [
            'AVAX_SDLC_ROOT' => '/tmp/fake-root',
        ]);
        self::assertSame(0, $result['exit_code']);
        self::assertSame('/tmp/fake-root', trim($result['stdout']));
    }

    #[Test]
    public function runCommandCapturesOutput(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        $result = SdlcRuntime::runCommand('echo hello');
        echo json_encode($result);
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        $data = json_decode(trim($result['stdout']), true);
        self::assertSame(0, $data['exit_code']);
        self::assertSame('hello', $data['output']);
    }

    #[Test]
    public function runCommandCapturesFailure(): void
    {
        $wrapper = $this->createTempScript(<<<'PHP'
        <?php
        require_once $argv[1];
        $result = SdlcRuntime::runCommand('exit 42');
        echo json_encode($result);
        PHP);

        $result = RunsToolingCommand::run($wrapper, [$this->script]);
        self::assertSame(0, $result['exit_code']);
        $data = json_decode(trim($result['stdout']), true);
        self::assertSame(42, $data['exit_code']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content, 'SdlcRuntime must not hardcode /usr/bin/git');
    }

    private function createTempScript(string $code): string
    {
        $path = sys_get_temp_dir() . '/avax-sdlc-test-' . uniqid() . '.php';
        file_put_contents($path, $code);
        return $path;
    }
}
