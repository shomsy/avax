<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Files\CopyFile;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Filesystem\Files\FileCopyFailed;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

class CopyFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $sourceFile;
    private string $destFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->sourceFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/copy_source.txt';
        $this->destFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/copy_dest.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->sourceFile);
        @unlink(filename: $this->destFile);
        parent::tearDown();
    }

    public function testExecuteCopiesFile() : void
    {
        file_put_contents(filename: $this->sourceFile, data: "source content\n");

        $result = (new CopyFile(disk: $this->disk))->execute(source: $this->sourceFile, destination: $this->destFile);

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->destFile);
    }

    public function testExecuteThrowsExceptionForNonExistentSource() : void
    {
        $this->expectException(exception: FileNotFound::class);

        (new CopyFile(disk: $this->disk))->execute(source: '/nonexistent.txt', destination: $this->destFile);
    }
}