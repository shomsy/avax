<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\DeleteFile;
use Avax\Filesystem\Files\FileDeleteFailed;
use PHPUnit\Framework\TestCase;

class DeleteFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $testFile;
    private string $testDirectory;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/delete_test.txt';
        $this->testDirectory = '/home/shomsy/projects/components/tests/fixtures/Filesystem/delete_test_dir';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        if (is_dir(filename: $this->testDirectory)) {
            $this->disk->deleteDirectory(path: $this->testDirectory);
        }

        parent::tearDown();
    }

    public function testExecuteReturnsTrueForNonExistentFile() : void
    {
        $result = (new DeleteFile(disk: $this->disk))->execute(path: '/ne postoji fajl.txt');

        self::assertTrue(condition: $result);
    }

    public function testExecuteDeletesExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadržaj\n");

        $result = (new DeleteFile(disk: $this->disk))->execute(path: $this->testFile);

        self::assertTrue(condition: $result);
        self::assertFileDoesNotExist(filename: $this->testFile);
    }

    public function testExecuteThrowsExceptionOnFailure() : void
    {
        mkdir(directory: $this->testDirectory, permissions: 0755, recursive: true);

        $this->expectException(exception: FileDeleteFailed::class);

        (new DeleteFile(disk: $this->disk))->execute(path: $this->testDirectory);
    }
}
