<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\DeleteDirectory;
use Avax\Filesystem\Directories\DirectoryDeleteFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use PHPUnit\Framework\TestCase;

class DeleteDirectoryTest extends TestCase
{
    private LocalDisk $disk;
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/delete_dir_test';
        @mkdir(directory: $this->testDir, permissions: 0755, recursive: true);
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function testExecuteReturnsTrueForNonExistentDirectory() : void
    {
        $result = (new DeleteDirectory(disk: $this->disk))->execute(path: '/ne postoji dir');

        self::assertTrue(condition: $result);
    }

    public function testExecuteDeletesExistingDirectory() : void
    {
        $result = (new DeleteDirectory(disk: $this->disk))->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertFalse(condition: is_dir(filename: $this->testDir));
    }
}