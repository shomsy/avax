<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\EnsureDirectoryExists;
use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

class EnsureDirectoryExistsTest extends TestCase
{
    private LocalDisk $disk;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk    = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/ensure_dir_test';
    }

    protected function tearDown(): void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }

    public function test_execute_creates_directory_if_not_exists(): void
    {
        $result = new EnsureDirectoryExists(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $this->testDir));
    }

    public function test_execute_returns_true_if_exists(): void
    {
        mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);

        $result = new EnsureDirectoryExists(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }
}
