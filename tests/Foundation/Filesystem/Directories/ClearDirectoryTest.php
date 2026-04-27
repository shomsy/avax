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

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/clear_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        parent::tearDown();
    }

    public function testExecuteThrowsExceptionForNonDirectory() : void
    {
        $this->expectException(exception: DirectoryClearFailed::class);

        new ClearDirectory(disk: $this->disk)->execute(path: '/ne postoji dir');
    }

    public function testExecuteClearsDirectoryContents() : void
    {
        file_put_contents(filename: $this->testDir . '/file1.txt', data: "sadrzaj\n");

        $result = new ClearDirectory(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertSame(['.', '..'], scandir(directory: $this->testDir));
    }
}
