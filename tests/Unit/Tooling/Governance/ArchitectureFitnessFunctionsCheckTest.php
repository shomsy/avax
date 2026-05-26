<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArchitectureFitnessFunctionsCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-architecture-fitness-functions.php';
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
        $result = RunsToolingCommand::run($this->script, ['--mode=invalid'], $this->root);
        self::assertNotSame(0, $result['exit_code']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }

    #[Test]
    public function sensitiveChangeWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-how-to.md', "content");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/some-how-to.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function sensitiveChangeWithValidEvidencePasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-how-to.md', "content");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Architecture Rule', 'Why This Rule Exists', 'Failure Mode Prevented', 'Fitness Function Type', 'Command / Review Procedure', 'Scope', 'Baseline Mode', 'Changed-Scope Mode', 'Full Mode', 'Expected Pass Signal', 'Expected Fail Signal', 'Evidence Path', 'Owner', 'Review Date'];
        $md = "# Architecture Fitness Function Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/architecture-fitness-functions.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/some-how-to.md .agents/management/evidence/generated/task-name/architecture-fitness-functions.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code'], $result['stdout']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function malformedFitnessEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-how-to.md', "content");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing "Review Date"
        $required = ['Architecture Rule', 'Why This Rule Exists', 'Failure Mode Prevented', 'Fitness Function Type', 'Command / Review Procedure', 'Scope', 'Baseline Mode', 'Changed-Scope Mode', 'Full Mode', 'Expected Pass Signal', 'Expected Fail Signal', 'Evidence Path', 'Owner'];
        $md = "# Architecture Fitness Function Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/architecture-fitness-functions.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/some-how-to.md .agents/management/evidence/generated/task-name/architecture-fitness-functions.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }
}
