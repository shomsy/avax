<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\CreateDirectory;
use Avax\Filesystem\Directories\DirectoryCreateFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use PHPUnit\Framework\TestCase;

class CreateDirectoryTest extends TestCase
{
    private LocalDisk $disk;
    private string $testDir;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/create_dir_test';
    }

    protected function tearDown() : void
    {
        @rmdir(directory: $this->testDir);
        parent::tearDown();
    }

    public function testExecuteCreatesDirectory() : void
    {
        $result = (new CreateDirectory(disk: $this->disk))->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $this->testDir));
    }

    public function testExecuteReturnsTrueIfDirectoryExists() : void
    {
        mkdir(directory: $this->testDir, permissions: 0755, recursive: true);

        $result = (new CreateDirectory(disk: $this->disk))->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testExecuteThrowsExceptionOnFailure() : void
    {
        $this->expectException(exception: DirectoryCreateFailed::class);

        (new CreateDirectory(disk: $this->disk))->execute(path: '/root/nemoguce');
    }
}