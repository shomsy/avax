<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\ClearDirectory;
use Avax\Filesystem\Directories\DirectoryClearFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

class ClearDirectoryTest extends TestCase
{
    private LocalDisk $disk;

    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk    = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/clear_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0o755, recursive: true);
    }

    protected function tearDown(): void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }

    public function test_execute_throws_exception_for_non_directory(): void
    {
        $this->expectException(exception: DirectoryClearFailed::class);

        new ClearDirectory(disk: $this->disk)->execute(path: '/ne postoji dir');
    }

    public function test_execute_clears_directory_contents(): void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "sadrzaj\n");

        $result = new ClearDirectory(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertSame(['.', '..'], scandir(directory: $this->testDir));
    }
}
