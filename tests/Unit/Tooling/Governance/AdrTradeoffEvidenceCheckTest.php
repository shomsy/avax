<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AdrTradeoffEvidenceCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-adr-tradeoff-evidence.php';
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
        self::assertTrue(str_contains($result['stdout'], 'GREEN') || str_contains($result['stdout'], 'RED') || str_contains($result['stdout'], 'YELLOW'));
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
    public function strictSensitiveChangeWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to/architecture';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-arch-how-to.md', "content");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/architecture/some-arch-how-to.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed', '--strict'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function strictSensitiveChangeWithValidEvidencePasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to/architecture';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-arch-how-to.md', "content");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Task', 'Decision', 'Context', 'Forces', 'Options Considered', 'Comparison Matrix', 'Chosen Option', 'Consequences', 'Reversibility', 'Fitness Function', 'Coupling Impact', 'Data Impact', 'Security / Runtime Impact', 'Accepted Debt', 'Owner', 'Review Date'];
        $md = "# ADR Trade-off Decision Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/adr-tradeoff-decision.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/architecture/some-arch-how-to.md .agents/management/evidence/generated/task-name/adr-tradeoff-decision.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed', '--strict'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code'], $result['stdout']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function malformedAdrEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/.agents/how-to/architecture';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/some-arch-how-to.md', "content");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing "Review Date"
        $required = ['Task', 'Decision', 'Context', 'Forces', 'Options Considered', 'Comparison Matrix', 'Chosen Option', 'Consequences', 'Reversibility', 'Fitness Function', 'Coupling Impact', 'Data Impact', 'Security / Runtime Impact', 'Accepted Debt', 'Owner'];
        $md = "# ADR Trade-off Decision Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/adr-tradeoff-decision.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/how-to/architecture/some-arch-how-to.md .agents/management/evidence/generated/task-name/adr-tradeoff-decision.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed', '--strict'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }
}
