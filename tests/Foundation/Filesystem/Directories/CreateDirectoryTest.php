<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Directories;

use Avax\Filesystem\Directories\CreateDirectory;
use Avax\Filesystem\Directories\DirectoryCreateFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;

class CreateDirectoryTest extends TestCase
{
    private LocalDisk $disk;
    private string $testDir;
    private string $conflictingFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testDir = '/home/shomsy/projects/components/tests/fixtures/Filesystem/create_dir_test';
        $this->conflictingFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/create_dir_conflict.txt';
    }

    protected function tearDown() : void
    {
        if (is_dir(filename: $this->testDir)) {
            $this->disk->deleteDirectory(path: $this->testDir);
        }

        @unlink(filename: $this->conflictingFile);
        parent::tearDown();
    }

    public function testExecuteCreatesDirectory() : void
    {
        $result = new CreateDirectory(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
        self::assertTrue(condition: is_dir(filename: $this->testDir));
    }

    public function testExecuteReturnsTrueIfDirectoryExists() : void
    {
        mkdir(directory: $this->testDir, permissions: 0755, recursive: true);

        $result = new CreateDirectory(disk: $this->disk)->execute(path: $this->testDir);

        self::assertTrue(condition: $result);
    }

    public function testExecuteThrowsExceptionOnFailure() : void
    {
        file_put_contents(filename: $this->conflictingFile, data: "conflict\n");

        $this->expectException(exception: DirectoryCreateFailed::class);

        new CreateDirectory(disk: $this->disk)->execute(path: $this->conflictingFile);
    }
}
