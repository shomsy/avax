<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RefactoringSafetyCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-refactoring-safety.php';
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
        $result = RunsToolingCommand::run($this->script, ['--mode=nope'], $this->root);
        self::assertNotSame(0, $result['exit_code']);
    }

    #[Test]
    public function evidenceTemplateExists(): void
    {
        self::assertFileExists($this->root . '/.agents/templates/evidence/refactoring-safety.md');
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->script);
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }

    #[Test]
    public function refactoringEvidenceWithRequiredHeadingsPasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n    public function myFunc() {}\n}");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/MyClass.php && " . escapeshellarg($git) . " commit -m 'initial'");

        // Modify the file to delete a method definition
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n}");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Task', 'Scope', 'Refactoring Type', 'Classes / Methods Affected', 'Behavioral Equivalence Proof', 'Tests Before', 'Tests After', 'Public API Impact', 'Coupling Impact', 'Runtime Impact', 'Rollback Plan', 'Review Date'];
        $md = "# Refactoring Safety Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/refactoring-safety.md', $md);

        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/management/evidence/generated/task-name/refactoring-safety.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function refactoringEvidenceMissingRequiredHeadingFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n    public function myFunc() {}\n}");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/MyClass.php && " . escapeshellarg($git) . " commit -m 'initial'");

        // Modify the file to delete a method definition
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n}");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing Review Date
        $required = ['Task', 'Scope', 'Refactoring Type', 'Classes / Methods Affected', 'Behavioral Equivalence Proof', 'Tests Before', 'Tests After', 'Public API Impact', 'Coupling Impact', 'Runtime Impact', 'Rollback Plan'];
        $md = "# Refactoring Safety Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/refactoring-safety.md', $md);

        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add .agents/management/evidence/generated/task-name/refactoring-safety.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }

    #[Test]
    public function methodDeletionsWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n    public function myFunc() {}\n}");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/MyClass.php && " . escapeshellarg($git) . " commit -m 'initial'");

        // Modify the file to delete the method definition
        file_put_contents($dir . '/MyClass.php', "<?php\nclass MyClass {\n}");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('class/method movement detected without refactoring-safety.md', $result['stdout']);
    }
}
