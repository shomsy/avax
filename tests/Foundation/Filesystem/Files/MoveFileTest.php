<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local\LocalDisk;
use Avax\Filesystem\Files\FileNotFound;
use Avax\Filesystem\Files\MoveFile;
use Avax\Tests\TestCase;

class MoveFileTest extends TestCase
{
    private LocalDisk $disk;

    private string $sourceFile;

    private string $destFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk     = new LocalDisk();
        $this->sourceFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/move_source.txt';
        $this->destFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/move_dest.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->sourceFile);
        @unlink(filename: $this->destFile);
        parent::tearDown();
    }

    public function test_execute_moves_file() : void
    {
        file_put_contents(filename: $this->sourceFile, data: "content\n");

        $result = new MoveFile(disk: $this->disk)->execute(source: $this->sourceFile, destination: $this->destFile);

        self::assertTrue(condition: $result);
        self::assertFileDoesNotExist(filename: $this->sourceFile);
        self::assertFileExists(filename: $this->destFile);
    }

    public function test_execute_throws_exception_for_non_existent_source() : void
    {
        $this->expectException(exception: FileNotFound::class);

        new MoveFile(disk: $this->disk)->execute(source: '/ne postoji.txt', destination: $this->destFile);
    }
}
