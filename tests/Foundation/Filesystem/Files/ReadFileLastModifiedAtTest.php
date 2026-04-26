<?php

declare(strict_types=1);

namespace components\Tests\Foundation\Filesystem\Files;

use components\Filesystem\Disks\Local\LocalDisk;
use components\Filesystem\Files\ReadFileLastModifiedAt;
use components\Tests\TestCase;
use PHPUnit\Framework\TestCase;

class ReadFileLastModifiedAtTest extends TestCase
{
    private LocalDisk $disk;
    private string $testFile;

    protected function setUp() : void
    {
        parent::setUp();
        $this->disk = new LocalDisk();
        $this->testFile = '/home/shomsy/projects/components/tests/fixtures/Filesystem/mtime_test.txt';
    }

    protected function tearDown() : void
    {
        @unlink(filename: $this->testFile);
        parent::tearDown();
    }

    public function testExecuteReturnsNullForNonExistentFile() : void
    {
        $result = new ReadFileLastModifiedAt(disk: $this->disk)->execute(path: '/ne postoji.txt');

        self::assertNull($result);
    }

    public function testExecuteReturnsTimestampForExistingFile() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = new ReadFileLastModifiedAt(disk: $this->disk)->execute(path: $this->testFile);

        self::assertIsInt($result);
        self::assertGreaterThan(0, $result);
    }
}
