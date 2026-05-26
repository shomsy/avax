<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Tooling\Sdlc;

use Avax\Tests\Support\Tooling\CreatesTemporaryGitRepository;
use Avax\Tests\Support\Tooling\RunsToolingCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for tooling/sdlc/GitChangedFiles.php
 *
 * Proves: modified file detection, staged file detection, untracked file detection,
 * duplicate exclusion, sorting, git failure handling.
 */
final class GitChangedFilesTest extends TestCase
{
    private string $sdlcDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sdlcDir = dirname(__DIR__, 4) . '/tooling/sdlc';
    }

    #[Test]
    public function returnsModifiedTrackedFiles(): void
    {
        $repo = (new CreatesTemporaryGitRepository('modified'))
            ->initGit()
            ->writeFile('hello.php', '<?php echo 1;')
            ->stageAll()
            ->commit('init');

        $repo->modifyFile('hello.php', '<?php echo 2;');

        $wrapper = $this->buildWrapper($repo->path());
        $result = RunsToolingCommand::run($wrapper, []);
        self::assertSame(0, $result['exit_code'], 'stdout: ' . $result['stdout'] . ' stderr: ' . $result['stderr']);

        $files = json_decode(trim($result['stdout']), true);
        self::assertContains('hello.php', $files);
    }

    #[Test]
    public function returnsStagedFiles(): void
    {
        $repo = (new CreatesTemporaryGitRepository('staged'))
            ->initGit()
            ->writeFile('first.php', '<?php')
            ->stageAll()
            ->commit('init')
            ->writeFile('second.php', '<?php echo 2;')
            ->stageAll();

        $wrapper = $this->buildWrapper($repo->path());
        $result = RunsToolingCommand::run($wrapper, []);
        self::assertSame(0, $result['exit_code'], 'stdout: ' . $result['stdout']);

        $files = json_decode(trim($result['stdout']), true);
        self::assertContains('second.php', $files);
    }

    #[Test]
    public function returnsUntrackedFiles(): void
    {
        $repo = (new CreatesTemporaryGitRepository('untracked'))
            ->initGit()
            ->writeFile('tracked.php', '<?php')
            ->stageAll()
            ->commit('init')
            ->writeFile('new-file.php', '<?php echo "new";');

        $wrapper = $this->buildWrapper($repo->path());
        $result = RunsToolingCommand::run($wrapper, []);
        self::assertSame(0, $result['exit_code'], 'stdout: ' . $result['stdout']);

        $files = json_decode(trim($result['stdout']), true);
        self::assertContains('new-file.php', $files);
    }

    #[Test]
    public function excludesDuplicatesAndSorts(): void
    {
        $repo = (new CreatesTemporaryGitRepository('dedup'))
            ->initGit()
            ->writeFile('b.php', '<?php')
            ->writeFile('a.php', '<?php')
            ->stageAll()
            ->commit('init')
            ->modifyFile('b.php', '<?php // changed')
            ->modifyFile('a.php', '<?php // changed');

        // Stage one file to create overlap between diff and diff --cached
        $repo->exec('git add a.php');

        $wrapper = $this->buildWrapper($repo->path());
        $result = RunsToolingCommand::run($wrapper, []);
        self::assertSame(0, $result['exit_code']);

        $files = json_decode(trim($result['stdout']), true);
        // Must be sorted and deduplicated
        self::assertSame(array_unique($files), $files, 'Must not contain duplicates');
        $sorted = $files;
        sort($sorted);
        self::assertSame($sorted, $files, 'Must be sorted');
    }

    #[Test]
    public function failsClearlyWhenGitUnavailable(): void
    {
        $repo = (new CreatesTemporaryGitRepository('nogit'))
            ->initGit()
            ->writeFile('test.php', '<?php')
            ->stageAll()
            ->commit('init');

        $wrapper = $this->buildWrapper($repo->path());
        $result = RunsToolingCommand::run($wrapper, [], null, [
            'AVAX_SDLC_DISABLE_GIT' => '1',
        ]);

        self::assertNotSame(0, $result['exit_code'], 'Must fail when git unavailable');
        self::assertStringContainsString('RED', $result['stdout']);
    }

    #[Test]
    public function doesNotHardcodeGitPath(): void
    {
        $content = file_get_contents($this->sdlcDir . '/GitChangedFiles.php');
        self::assertStringNotContainsString('/usr/bin/git', $content);
    }

    private function buildWrapper(string $repoPath): string
    {
        $sdlcDir = $this->sdlcDir;
        $code = <<<PHP
        <?php
        putenv('AVAX_SDLC_ROOT={$repoPath}');
        require_once '{$sdlcDir}/GitChangedFiles.php';
        echo json_encode(GitChangedFiles::all('{$repoPath}'));
        PHP;

        $path = sys_get_temp_dir() . '/avax-git-changed-test-' . uniqid() . '.php';
        file_put_contents($path, $code);
        return $path;
    }
}
