<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ScenarioInputCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-scenario-input.php';
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
    public function productionChangeWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Something.php', "<?php // production code change");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Something.php");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function validScenarioEvidencePasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Something.php', "<?php // production code change");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $md = "# Scenario Input Evidence\n\n";
        $required = [
            'Task' => 'Task name',
            'Scope' => 'Scope description',
            'System Boundary' => 'System boundary description',
            'Primary Actor' => 'Human user',
            'Actor Goal' => 'Run the task',
            'Stakeholders and Interests' => 'Developer',
            'Preconditions' => 'System is ready',
            'Success Guarantees' => 'Runs fine',
            'Minimal Failure Guarantees' => 'Rolls back',
            'Main Success Scenario' => "1. Do step one\n2. Do step two\n3. Do step three",
            'Extension / Failure Paths' => 'No failure paths',
            'Security Sensitivity' => 'NO',
            'Data / State Mutation Sensitivity' => 'NO',
            'Runtime / Concurrency Sensitivity' => 'NO',
            'Observability Requirement' => 'Logs are written',
            'Acceptance Criteria' => 'Test passes',
            'Planned Tests' => 'Unit tests',
            'Out of Scope' => 'Everything else',
        ];

        foreach ($required as $heading => $content) {
            $md .= "## {$heading}\n{$content}\n\n";
        }
        file_put_contents($evDir . '/scenario-input.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Something.php .agents/management/evidence/generated/task-name/scenario-input.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertSame(0, $result['exit_code'], $result['stdout']);
        self::assertStringContainsString('GREEN', $result['stdout']);
    }

    #[Test]
    public function missingHeadingFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Something.php', "<?php // production code change");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Leave out "Acceptance Criteria"
        $md = "# Scenario Input Evidence\n\n";
        $required = [
            'Task' => 'Task name',
            'Scope' => 'Scope description',
            'System Boundary' => 'System boundary description',
            'Primary Actor' => 'Human user',
            'Actor Goal' => 'Run the task',
            'Stakeholders and Interests' => 'Developer',
            'Preconditions' => 'System is ready',
            'Success Guarantees' => 'Runs fine',
            'Minimal Failure Guarantees' => 'Rolls back',
            'Main Success Scenario' => "1. Do step one\n2. Do step two\n3. Do step three",
            'Extension / Failure Paths' => 'No failure paths',
            'Security Sensitivity' => 'NO',
            'Data / State Mutation Sensitivity' => 'NO',
            'Runtime / Concurrency Sensitivity' => 'NO',
            'Observability Requirement' => 'Logs are written',
            'Planned Tests' => 'Unit tests',
            'Out of Scope' => 'Everything else',
        ];

        foreach ($required as $heading => $content) {
            $md .= "## {$heading}\n{$content}\n\n";
        }
        file_put_contents($evDir . '/scenario-input.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Something.php .agents/management/evidence/generated/task-name/scenario-input.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('missing headings: Acceptance Criteria', $result['stdout']);
    }

    #[Test]
    public function insufficientStepsFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/Something.php', "<?php // production code change");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Only 2 steps
        $md = "# Scenario Input Evidence\n\n";
        $required = [
            'Task' => 'Task name',
            'Scope' => 'Scope description',
            'System Boundary' => 'System boundary description',
            'Primary Actor' => 'Human user',
            'Actor Goal' => 'Run the task',
            'Stakeholders and Interests' => 'Developer',
            'Preconditions' => 'System is ready',
            'Success Guarantees' => 'Runs fine',
            'Minimal Failure Guarantees' => 'Rolls back',
            'Main Success Scenario' => "1. Do step one\n2. Do step two",
            'Extension / Failure Paths' => 'No failure paths',
            'Security Sensitivity' => 'NO',
            'Data / State Mutation Sensitivity' => 'NO',
            'Runtime / Concurrency Sensitivity' => 'NO',
            'Observability Requirement' => 'Logs are written',
            'Acceptance Criteria' => 'Test passes',
            'Planned Tests' => 'Unit tests',
            'Out of Scope' => 'Everything else',
        ];

        foreach ($required as $heading => $content) {
            $md .= "## {$heading}\n{$content}\n\n";
        }
        file_put_contents($evDir . '/scenario-input.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/Something.php .agents/management/evidence/generated/task-name/scenario-input.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('Main Success Scenario must have between 3 and 9 numbered steps', $result['stdout']);
    }
}
