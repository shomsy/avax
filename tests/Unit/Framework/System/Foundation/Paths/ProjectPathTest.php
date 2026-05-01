<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\System\Foundation\Paths;

use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Paths\RuntimePath;
use Avax\Tests\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ProjectPath::class)]
#[CoversClass(RuntimePath::class)]
final class ProjectPathTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/avax_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }

    #[Test]
    public function it_normalizes_path_and_removes_trailing_slash(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir . '/');

        self::assertSame($this->tempDir, $projectPath->toString());
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $projectPath = new ProjectPath(value: '  ' . $this->tempDir . '  ');

        self::assertSame($this->tempDir, $projectPath->toString());
    }

    #[Test]
    public function it_throws_for_empty_path(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Project path cannot be empty.');

        new ProjectPath(value: '   ');
    }

    #[Test]
    public function it_throws_for_nonexistent_path(): void
    {
        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Project path "/nonexistent/path" does not exist.');

        new ProjectPath(value: '/nonexistent/path');
    }

    #[Test]
    public function it_joins_relative_paths(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir);

        $joined = $projectPath->join(relativePath: 'storage/framework/cache');

        self::assertSame($this->tempDir . '/storage/framework/cache', $joined);
    }

    #[Test]
    public function it_joins_absolute_path_from_root(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir);

        $joined = $projectPath->join(relativePath: '/var/run');

        self::assertSame('/var/run', $joined);
    }

    #[Test]
    public function it_normalizes_runtime_path_from_project(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir);

        $runtimePath = new RuntimePath(
            value: 'runtime/',
            projectPath: $projectPath,
        );

        self::assertSame($this->tempDir . '/runtime', $runtimePath->toString());
    }

    #[Test]
    public function it_uses_absolute_runtime_path_when_prefixed(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir);

        $runtimePath = new RuntimePath(
            value: '/var/run/avax',
            projectPath: $projectPath,
        );

        self::assertSame('/var/run/avax', $runtimePath->toString());
    }

    #[Test]
    public function it_throws_for_empty_runtime_path(): void
    {
        $projectPath = new ProjectPath(value: $this->tempDir);

        $this->expectException(FrameworkMisconfigured::class);
        $this->expectExceptionMessage('Runtime path cannot be empty.');

        new RuntimePath(value: '   ', projectPath: $projectPath);
    }
}
