<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Filesystem\Files;

use Avax\Tests\TestCase;
use components\Filesystem\Disks\Local\LocalDisk;
use components\Filesystem\Files\ReadFileLastModifiedAt;

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

    public function test_execute_returns_null_for_non_existent_file() : void
    {
        $result = new ReadFileLastModifiedAt(disk: $this->disk)->execute(path: '/ne postoji.txt');

        self::assertNull($result);
    }

    public function test_execute_returns_timestamp_for_existing_file() : void
    {
        file_put_contents(filename: $this->testFile, data: "sadrzaj\n");

        $result = new ReadFileLastModifiedAt(disk: $this->disk)->execute(path: $this->testFile);

        self::assertIsInt($result);
        self::assertGreaterThan(0, $result);
    }
}
