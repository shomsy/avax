<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ConstructionChecklistCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-construction-checklist.php';
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
        $result = RunsToolingCommand::run($this->script, ['--mode=unsupported'], $this->root);
        self::assertNotSame(0, $result['exit_code']);
    }

    #[Test]
    public function evidenceTemplateExists(): void
    {
        self::assertFileExists($this->root . '/.agents/templates/evidence/construction-checklist.md');
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }

    #[Test]
    public function constructionEvidenceWithRequiredHeadingsPasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Simple.php', "<?php // construction");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Task', 'Scope', 'Naming Discipline', 'Minimal Viable Construction', 'Encapsulation', 'Method Length and Complexity', 'Guard Clauses', 'Error Handling', 'Comments vs Self-Explaining Code', 'Tests', 'Review Date'];
        $md = "# Construction Checklist\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/construction-checklist.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Simple.php .agents/management/evidence/generated/task-name/construction-checklist.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function constructionEvidenceMissingRequiredHeadingFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Simple.php', "<?php // construction");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing Review Date
        $required = ['Task', 'Scope', 'Naming Discipline', 'Minimal Viable Construction', 'Encapsulation', 'Method Length and Complexity', 'Guard Clauses', 'Error Handling', 'Comments vs Self-Explaining Code', 'Tests'];
        $md = "# Construction Checklist\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/construction-checklist.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Simple.php .agents/management/evidence/generated/task-name/construction-checklist.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }

    #[Test]
    public function changedProductionFileWithoutEvidenceInStrictModeFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Simple.php', "<?php // construction");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Simple.php");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed', '--strict'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('Changed production PHP files require', $result['stdout']);
    }

    #[Test]
    public function changedProductionFileWithoutEvidenceInNonStrictModeWarns(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Simple.php', "<?php // construction");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Simple.php");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code']);
        self::assertStringContainsString('YELLOW', $result['stdout']);
        self::assertStringContainsString('Changed production PHP files require', $result['stdout']);
    }
}
