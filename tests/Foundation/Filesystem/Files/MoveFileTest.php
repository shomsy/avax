<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Files\MoveFile;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

class MoveFileTest extends TestCase
{
    private LocalDisk $disk;
    private string $sourceFile;
    private string $destFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->sourceFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/move_source.txt';
        $this->destFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/move_dest.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->sourceFile);
        @unlink(filename: $this->destFile);
        parent::tearDown();
    }

    public function testExecuteMovesFile() : void
    {
        file_put_contents(filename: $this->sourceFile, data: "content\n");

        $result = (new MoveFile(disk: $this->disk))->execute(source: $this->sourceFile, destination: $this->destFile);

        self::assertTrue(condition: $result);
        self::assertFileDoesNotExist(filename: $this->sourceFile);
        self::assertFileExists(filename: $this->destFile);
    }

    public function testExecuteThrowsExceptionForNonExistentSource() : void
    {
        $this->expectException(exception: FileNotFound::class);

        (new MoveFile(disk: $this->disk))->execute(source: '/ne postoji.txt', destination: $this->destFile);
    }
}