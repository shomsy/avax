<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Governance;

use Avax\Tests\Support\Tooling\CreatesTempGitRepo;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnterpriseApplicationBoundariesCheckTest extends TestCase
{
    use CreatesTempGitRepo;

    private string $script;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 4);
        $this->script = $this->root . '/tooling/governance/check-enterprise-application-boundaries.php';
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
        self::assertFileExists($this->root . '/.agents/templates/evidence/enterprise-application-boundary.md');
    }

    #[Test]
    public function detectsPersistenceBoundarySignals(): void
    {
        $content = file_get_contents($this->script);
        foreach (['Repository', 'Gateway', 'Mapper', 'UnitOfWork', 'Transaction', 'Database', 'Persistence'] as $signal) {
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
    public function boundarySensitiveFileWithoutEvidenceFails(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/UserRepository.php', "<?php // User persistence");

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/UserRepository.php");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function boundarySensitiveFileWithValidEvidencePasses(): void
    {
        $temp = $this->createTempGitRepo();
        $dir = $temp . '/components';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/UserRepository.php', "<?php // User persistence");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        $required = ['Task', 'Application Flow', 'Domain Rule', 'Transaction Boundary', 'Persistence Boundary', 'Data Mapping Strategy', 'State Ownership', 'Pattern Chosen', 'Simpler Alternative', 'Why Chosen', 'Consequences', 'Tests / Evidence', 'Review Date'];
        $md = "# Enterprise Application Boundary Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/enterprise-application-boundary.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/UserRepository.php .agents/management/evidence/generated/task-name/enterprise-application-boundary.md");

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
        file_put_contents($dir . '/UserRepository.php', "<?php // User persistence");

        $evDir = $temp . '/.agents/management/evidence/generated/task-name';
        mkdir($evDir, 0777, true);

        // Missing 'Review Date'
        $required = ['Task', 'Application Flow', 'Domain Rule', 'Transaction Boundary', 'Persistence Boundary', 'Data Mapping Strategy', 'State Ownership', 'Pattern Chosen', 'Simpler Alternative', 'Why Chosen', 'Consequences', 'Tests / Evidence'];
        $md = "# Enterprise Application Boundary Evidence\n\n";
        foreach ($required as $heading) {
            $md .= "## {$heading}\ncontent\n\n";
        }
        file_put_contents($evDir . '/enterprise-application-boundary.md', $md);

        $git = trim((string) shell_exec('command -v git 2>/dev/null')) ?: 'git';
        shell_exec("cd " . escapeshellarg($temp) . " && " . escapeshellarg($git) . " add components/UserRepository.php .agents/management/evidence/generated/task-name/enterprise-application-boundary.md");

        $result = RunsToolingCommand::run($this->script, ['--mode=changed'], $this->root, ['AVAX_SDLC_ROOT' => $temp]);
        self::assertNotSame(0, $result['exit_code']);
        self::assertStringContainsString('RED', $result['stdout']);
        self::assertStringContainsString('missing heading: Review Date', $result['stdout']);
    }
}
