<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Filesystem\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\CopyFile;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Tests\TestCase;

class CopyFileTest extends TestCase
{
    private LocalDisk $disk;

    private string $sourceFile;

    private string $destFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk;
        $this->sourceFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/copy_source.txt';
        $this->destFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/copy_dest.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->sourceFile);
        @unlink(filename: $this->destFile);
        parent::tearDown();
    }

    public function test_execute_copies_file() : void
    {
        file_put_contents(filename: $this->sourceFile, data: "source content\n");

        $result = new CopyFile(disk: $this->disk)->execute(source: $this->sourceFile, destination: $this->destFile);

        self::assertTrue(condition: $result);
        self::assertFileExists(filename: $this->destFile);
    }

    public function test_execute_throws_exception_for_non_existent_source() : void
    {
        $this->expectException(exception: FileNotFound::class);

        new CopyFile(disk: $this->disk)->execute(source: '/nonexistent.txt', destination: $this->destFile);
    }
}
