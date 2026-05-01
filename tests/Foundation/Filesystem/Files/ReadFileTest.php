<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Filesystem\Files\ReadFile;
use Avax\Tests\TestCase;

class ReadFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/read_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function testExecuteReturnsFileContents() : void
    {
        file_put_contents(filename: $this->testFile, data: "test content\n");

        $result = new ReadFile(disk: $this->disk)->execute(path: $this->testFile);

        self::assertSame("test content\n", $result);
    }

    public function testExecuteThrowsExceptionForNonExistentFile() : void
    {
        $this->expectException(exception: FileNotFound::class);
        $this->expectExceptionMessage(message: 'File not found:');

        new ReadFile(disk: $this->disk)->execute(path: '/nonexistent/file.txt');
    }

    public function testExecuteThrowsExceptionForUnreadableFile() : void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped(reason: 'Cannot test unreadable files as root');
        }

        $this->expectException(exception: FileNotFound::class);

        file_put_contents(filename: $this->testFile, data: "content\n");
        chmod(filename: $this->testFile, permissions: 0o000);

        try {
            new ReadFile(disk: $this->disk)->execute(path: $this->testFile);
        } finally {
            chmod(filename: $this->testFile, permissions: 0o644);
        }
    }
}
