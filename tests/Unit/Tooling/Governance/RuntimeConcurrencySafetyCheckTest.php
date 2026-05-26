<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RuntimeConcurrencySafetyCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-runtime-concurrency-safety.php';
    }

    protected function tearDown(): void
    {
        $this->destroyTempGitRepo();
        parent::tearDown();
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
        self::assertTrue(str_contains($result['stdout'], 'GREEN') || str_contains($result['stdout'], 'RED'));
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
    public function evidenceTemplateExists(): void
    {
        self::assertFileExists($this->root . '/.agents/templates/evidence/runtime-concurrency-safety.md');
    }

    #[Test]
    public function detectsRuntimeConcurrencySignals(): void
    {
        $content = file_get_contents($this->script);
        foreach (['Runtime', 'Worker', 'Fiber', 'Async', 'Process', 'Queue', 'Stream', 'Reset', 'Scope'] as $signal) {
            self::assertStringContainsString($signal, $content, "Must detect: {$signal}");
        }
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }

    #[Test]
    public function sensitiveFileWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/SwooleWorker.php', "<?php // Worker runtime");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/SwooleWorker.php");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function sensitiveFileWithValidEvidencePasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/SwooleWorker.php', "<?php // Worker runtime");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Task', 'Runtime Model', 'Request Scope', 'Shared State', 'Reset Behavior', 'Concurrency Model', 'Timeout / Cancellation', 'Retry / Idempotency', 'Backpressure', 'Runtime Adapter Boundary', 'Runtime API Leak Risk', 'Observability', 'Tests / Evidence', 'Review Date'];
        $md = "# Runtime / Concurrency Safety Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/runtime-concurrency-safety.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/SwooleWorker.php .agents/management/evidence/generated/task-name/runtime-concurrency-safety.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function malformedEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/SwooleWorker.php', "<?php // Worker runtime");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing 'Review Date'
        $required = ['Task', 'Runtime Model', 'Request Scope', 'Shared State', 'Reset Behavior', 'Concurrency Model', 'Timeout / Cancellation', 'Retry / Idempotency', 'Backpressure', 'Runtime Adapter Boundary', 'Runtime API Leak Risk', 'Observability', 'Tests / Evidence'];
        $md = "# Runtime / Concurrency Safety Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/runtime-concurrency-safety.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/SwooleWorker.php .agents/management/evidence/generated/task-name/runtime-concurrency-safety.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }
}
