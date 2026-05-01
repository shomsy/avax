<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\ListDirectoryFiles;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

final class ListDirectoryFilesTest extends TestCase
{
    private LocalDisk $disk;

    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk;
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/list_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }

    public function test_execute_returns_empty_array_for_non_existent_directory() : void
    {
        $result = new ListDirectoryFiles(disk: $this->disk)->execute(path: '/ne postoji dir');

        self::assertSame([], $result);
    }

    public function test_execute_returns_file_paths() : void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "sadrzaj1\n");
        file_put_contents(filename: $this->testDir . '/file2.txt', data: "sadrzaj2\n");

        $result = new ListDirectoryFiles(disk: $this->disk)->execute(path: $this->testDir);

        self::assertCount(2, $result);
    }
}
