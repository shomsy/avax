<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Files\DeleteFile;
use Avax\Filesystem\Files\FileDeleteFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use PHPUnit\Framework\TestCase;

class DeleteFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/delete_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
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
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test delete failure as root');
        }

        $this->expectException(exception: FileDeleteFailed::class);

        (new DeleteFile(disk: $this->disk))->execute(path: '/root/nemoguce');
    }
}